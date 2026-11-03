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
                // Validate passwords first
                $password = $request->request->get('password');
                $confirmPassword = $request->request->get('confirmPassword');

                if ($password !== $confirmPassword) {
                    $this->addFlash('error', 'Passwords do not match.');
                    return $this->render('student/register.html.twig', [
                        'campuses' => $campuses,
                    ]);
                }

                if (strlen($password) < 6) {
                    $this->addFlash('error', 'Password must be at least 6 characters.');
                    return $this->render('student/register.html.twig', [
                        'campuses' => $campuses,
                    ]);
                }

                // Check if email already exists
                $existingStudent = $entityManager->getRepository(Student::class)
                    ->findOneBy(['email' => $request->request->get('email')]);
                if ($existingStudent) {
                    $this->addFlash('error', 'Email address is already registered.');
                    return $this->render('student/register.html.twig', [
                        'campuses' => $campuses,
                    ]);
                }

                // Check if student number already exists
                $existingStudentNumber = $entityManager->getRepository(Student::class)
                    ->findOneBy(['studentNumber' => $request->request->get('studentNumber')]);
                if ($existingStudentNumber) {
                    $this->addFlash('error', 'Student number is already registered.');
                    return $this->render('student/register.html.twig', [
                        'campuses' => $campuses,
                    ]);
                }

                // Create new student
                $student = new Student();

                // Personal Information
                $student->setStudentNumber($request->request->get('studentNumber'));
                $student->setFirstName($request->request->get('firstName'));
                $student->setMiddleName($request->request->get('middleName'));
                $student->setLastName($request->request->get('lastName'));
                $student->setSex($request->request->get('sex'));

                $birthDate = $request->request->get('birthDate');
                if ($birthDate) {
                    $student->setBirthDate(new \DateTime($birthDate));
                }

                $student->setCivilStatus($request->request->get('civilStatus'));
                $student->setReligion($request->request->get('religion'));

                // Contact Information
                $student->setEmail($request->request->get('email'));
                $student->setContactNumber($request->request->get('contactNumber'));
                $student->setAddress($request->request->get('address'));

                // Academic Information
                $student->setCampus($request->request->get('campus'));
                $student->setCourse($request->request->get('course'));
                $student->setYearLevel($request->request->get('yearLevel'));
                $student->setDepartment($request->request->get('department'));

                // Family Information
                $student->setFatherName($request->request->get('fatherName'));
                $student->setFatherOccupation($request->request->get('fatherOccupation'));
                $student->setMotherName($request->request->get('motherName'));
                $student->setMotherOccupation($request->request->get('motherOccupation'));
                $student->setParentsAddress($request->request->get('parentsAddress'));

                $familyMonthlyIncome = $request->request->get('familyMonthlyIncome');
                if ($familyMonthlyIncome !== null && $familyMonthlyIncome !== '') {
                    $student->setFamilyMonthlyIncome((float)$familyMonthlyIncome);
                }

                // Set scholarship type to "Pending Assignment" - admin will assign actual scholarship
                $student->setScholarshipType('Pending Assignment');

                // Handle Load Slip PDF Upload
                $loadSlipFile = $request->files->get('loadSlip');
                if ($loadSlipFile) {
                    // Validate file
                    if ($loadSlipFile->getSize() > 5 * 1024 * 1024) { // 5MB limit
                        $this->addFlash('error', 'Load slip file size must not exceed 5MB.');
                        return $this->render('student/register.html.twig', [
                            'campuses' => $campuses,
                        ]);
                    }

                    if ($loadSlipFile->getMimeType() !== 'application/pdf') {
                        $this->addFlash('error', 'Load slip must be a PDF file.');
                        return $this->render('student/register.html.twig', [
                            'campuses' => $campuses,
                        ]);
                    }

                    $originalFilename = pathinfo($loadSlipFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $loadSlipFile->guessExtension();

                    try {
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/load_slips';

                        // Create directory if it doesn't exist
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $loadSlipFile->move($uploadDir, $newFilename);
                        $student->setLoadSlipPath($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Failed to upload load slip. Please try again.');
                        return $this->render('student/register.html.twig', [
                            'campuses' => $campuses,
                        ]);
                    }
                }

                // Hash password
                $hashedPassword = $passwordHasher->hashPassword($student, $password);
                $student->setPassword($hashedPassword);

                // Set default values - pending admin approval
                $student->setRegistrationStatus('Pending');
                $student->setIsActive(false); // Admin needs to activate
                $student->setIsEmailVerified(false);
                $student->setRoles(['ROLE_STUDENT']);

                $entityManager->persist($student);
                $entityManager->flush();

                $this->addFlash('success', 'Registration successful! Your account is pending admin approval. You will be notified once your account is activated.');
                return $this->redirectToRoute('student_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
            }
        }

        return $this->render('student/register.html.twig', [
            'campuses' => $campuses,
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
