<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\AnnouncementRepository;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class StudentController extends AbstractController
{
    #[Route('/student/login', name: 'student_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If student is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('student_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('student/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/student/register', name: 'student_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger,
        CampusRepository $campusRepository
    ): Response {
        // If student is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('student_dashboard');
        }

        $campuses = $campusRepository->findAllActive();
        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            try {
                // Create new student
                $student = new Student();

                // Basic Information
                $student->setStudentNumber($request->request->get('student_number'));
                $student->setFirstName($request->request->get('first_name'));
                $student->setMiddleName($request->request->get('middle_name'));
                $student->setLastName($request->request->get('last_name'));
                $student->setExtensionName($request->request->get('extension_name'));
                $student->setDateOfBirth(new \DateTime($request->request->get('date_of_birth')));
                $student->setPlaceOfBirth($request->request->get('place_of_birth'));
                $student->setSex($request->request->get('sex'));
                $student->setCivilStatus($request->request->get('civil_status'));
                $student->setReligion($request->request->get('religion'));

                // Academic Information
                $student->setCampus($request->request->get('campus'));
                $student->setCourse($request->request->get('course'));
                $student->setYearLevel($request->request->get('year_level'));
                $student->setSemester($request->request->get('semester'));
                $student->setAcademicYear($request->request->get('academic_year'));

                // Set scholarship type to "Pending" - admin will assign actual scholarship
                $student->setScholarshipType('Pending Assignment');

                // Contact Information
                $student->setEmail($request->request->get('email'));
                $student->setContactNumber($request->request->get('contact_number'));
                $student->setAlternateContactNumber($request->request->get('alternate_contact_number'));

                // Address
                $student->setPermanentAddress($request->request->get('permanent_address'));
                $student->setCurrentAddress($request->request->get('current_address'));
                $student->setBarangay($request->request->get('barangay'));
                $student->setMunicipality($request->request->get('municipality'));
                $student->setProvince($request->request->get('province'));
                $student->setZipCode($request->request->get('zip_code'));

                // Family Information
                $student->setFatherName($request->request->get('father_name'));
                $student->setFatherOccupation($request->request->get('father_occupation'));
                $student->setFatherContactNumber($request->request->get('father_contact_number'));
                $student->setMotherName($request->request->get('mother_name'));
                $student->setMotherOccupation($request->request->get('mother_occupation'));
                $student->setMotherContactNumber($request->request->get('mother_contact_number'));
                $student->setGuardianName($request->request->get('guardian_name'));
                $student->setGuardianRelationship($request->request->get('guardian_relationship'));
                $student->setGuardianContactNumber($request->request->get('guardian_contact_number'));
                $student->setNumberOfSiblings($request->request->get('number_of_siblings'));
                $student->setFamilyMonthlyIncome($request->request->get('family_monthly_income'));

                // Handle Load Slip PDF Upload
                $loadSlipFile = $request->files->get('load_slip');
                if ($loadSlipFile) {
                    $originalFilename = pathinfo($loadSlipFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $loadSlipFile->guessExtension();

                    try {
                        $loadSlipFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/load_slips',
                            $newFilename
                        );
                        $student->setLoadSlipPath($newFilename);
                    } catch (FileException $e) {
                        $error = 'Failed to upload load slip. Please try again.';
                    }
                }

                // Hash password
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirm_password');

                if ($password !== $confirmPassword) {
                    $error = 'Passwords do not match.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } else {
                    $hashedPassword = $passwordHasher->hashPassword($student, $password);
                    $student->setPassword($hashedPassword);

                    // Set status - pending admin approval
                    $student->setRegistrationStatus('Not Enrolled');
                    $student->setIsActive(false); // Admin needs to activate
                    $student->setIsEmailVerified(false);
                    $student->setRoles(['ROLE_STUDENT']);

                    if (!$error) {
                        $entityManager->persist($student);
                        $entityManager->flush();

                        $success = true;
                        $this->addFlash('success', 'Registration successful! Please wait for admin approval.');
                    }
                }
            } catch (\Exception $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }

        return $this->render('student/register.html.twig', [
            'campuses' => $campuses,
            'error' => $error,
            'success' => $success,
        ]);
    }

    #[Route('/student/logout', name: 'student_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/student/dashboard', name: 'student_dashboard')]
    public function dashboard(AnnouncementRepository $announcementRepository): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        // Get active announcements
        $announcements = $announcementRepository->findActiveAnnouncements();

        return $this->render('student/dashboard.html.twig', [
            'student' => $student,
            'announcements' => $announcements,
        ]);
    }
}
