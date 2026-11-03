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
                $student->setLrn($request->request->get('lrn'));
                $student->setFirstName($request->request->get('firstName'));
                $student->setMiddleName($request->request->get('middleName'));
                $student->setLastName($request->request->get('lastName'));
                $student->setSuffix($request->request->get('suffix'));
                $student->setSex($request->request->get('sex'));

                $birthDate = $request->request->get('birthDate');
                if ($birthDate) {
                    $student->setDateOfBirth(new \DateTime($birthDate));
                }

                $student->setPlaceOfBirth($request->request->get('birthPlace'));
                $student->setCivilStatus($request->request->get('civilStatus'));

                // Contact Information
                $student->setEmail($request->request->get('email'));
                $student->setContactNumber($request->request->get('contactNumber'));

                // Address
                $student->setStreet($request->request->get('street'));
                $student->setBarangay($request->request->get('barangay'));
                $student->setMunicipality($request->request->get('municipality'));
                $student->setProvince($request->request->get('province'));
                $student->setCountry($request->request->get('country') ?: 'Philippines');

                // Build full address
                $addressParts = array_filter([
                    $request->request->get('street'),
                    $request->request->get('barangay'),
                    $request->request->get('municipality'),
                    $request->request->get('province')
                ]);
                $student->setPermanentAddress(implode(', ', $addressParts));

                // Academic Information
                $student->setCampus($request->request->get('campus'));
                $student->setCourse($request->request->get('course'));
                $student->setYearLevel($request->request->get('yearLevel'));
                $student->setDepartment($request->request->get('department'));
                $student->setSemester('1st Semester');
                $student->setAcademicYear(date('Y') . '-' . (date('Y') + 1));

                // Educational Background
                $student->setElementary($request->request->get('elementary'));
                $student->setElementaryYear($request->request->get('elementaryYear'));
                $student->setHighSchool($request->request->get('highSchool'));
                $student->setHighSchoolYear($request->request->get('highSchoolYear'));

                // Family Information
                $student->setGuardianName($request->request->get('guardianName'));
                $student->setGuardianContactNumber($request->request->get('guardianContactNumber'));
                $student->setFatherName($request->request->get('fatherName'));
                $student->setFatherContactNumber($request->request->get('fatherContactNumber'));
                $student->setMotherName($request->request->get('motherName'));
                $student->setMotherContactNumber($request->request->get('motherContactNumber'));
                $student->setSpouseName($request->request->get('spouseName'));
                $student->setSpouseContactNumber($request->request->get('spouseContactNumber'));

                $familyMonthlyIncome = $request->request->get('familyMonthlyIncome');
                if ($familyMonthlyIncome !== null && $familyMonthlyIncome !== '') {
                    $student->setFamilyMonthlyIncome((string)$familyMonthlyIncome);
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

        return $this->render('student/dashboard_new.html.twig', [
            'student' => $student,
            'announcements' => $announcements,
        ]);
    }

    #[Route('/student/dashboard-new', name: 'student_dashboard_new')]
    public function dashboardNew(AnnouncementRepository $announcementRepository): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        // Get active announcements
        $announcements = $announcementRepository->findActiveAnnouncements();

        return $this->render('student/dashboard_new.html.twig', [
            'student' => $student,
            'announcements' => $announcements,
        ]);
    }

    #[Route('/student/profile', name: 'student_profile')]
    public function profile(): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        return $this->render('student/profile.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/student/announcements', name: 'student_announcements')]
    public function announcements(AnnouncementRepository $announcementRepository): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        $announcements = $announcementRepository->findActiveAnnouncements();

        return $this->render('student/announcements.html.twig', [
            'announcements' => $announcements,
        ]);
    }

    #[Route('/student/notifications', name: 'student_notifications')]
    public function notifications(): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        return $this->render('student/notifications.html.twig');
    }

    #[Route('/student/documents', name: 'student_documents')]
    public function documents(): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        return $this->render('student/documents.html.twig');
    }

    #[Route('/student/authorization', name: 'student_authorization')]
    public function authorization(): Response
    {
        $student = $this->getUser();

        if (!$student instanceof Student) {
            return $this->redirectToRoute('student_login');
        }

        return $this->render('student/authorization.html.twig', [
            'student' => $student,
        ]);
    }
}
