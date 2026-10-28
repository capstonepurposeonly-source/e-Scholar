<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If user is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $response = $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);

        // Disable browser caching to prevent bfcache issues
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }


    #[Route('/admin/register', name: 'admin_register')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        // If user is already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        $error = null;
        $success = false;
        $fullName = '';
        $email = '';

        if ($request->isMethod('POST')) {
            $fullName = $request->request->get('full_name');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validate inputs
            if (empty($fullName) || empty($email) || empty($password)) {
                $error = 'All fields are required.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Check if email already exists
                $existingAdmin = $entityManager->getRepository(\App\Entity\Admin::class)
                    ->findOneBy(['email' => $email]);

                if ($existingAdmin) {
                    $error = 'An admin with this email already exists.';
                } else {
                    // Create new admin
                    $admin = new \App\Entity\Admin();
                    $admin->setFullName($fullName);
                    $admin->setEmail($email);

                    // Hash the password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $admin->setPassword($hashedPassword);

                    $entityManager->persist($admin);
                    $entityManager->flush();

                    $success = true;
                }
            }
        }

        $response = $this->render('admin/register.html.twig', [
            'error' => $error,
            'success' => $success,
            'full_name' => $fullName,
            'email' => $email,
        ]);

        // Add cache prevention headers
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        return $response;
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        StudentRepository $studentRepository,
        \App\Repository\CampusRepository $campusRepository,
        \App\Repository\LiquidationRepository $liquidationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $conn = $entityManager->getConnection();

        // Get campus statistics using raw SQL
        $sql = 'SELECT c.campus_name, COUNT(s.id) as student_count 
                FROM campuses c 
                LEFT JOIN students s ON c.campus_name = s.campus 
                WHERE c.is_active = 1 
                GROUP BY c.id, c.campus_name 
                ORDER BY c.campus_name';
        $result = $conn->executeQuery($sql);

        $campusStats = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $campusStats[$row['campus_name']] = (int)$row['student_count'];
        }

        // Get total students
        $sql = 'SELECT COUNT(*) as total FROM students';
        $totalStudents = (int)$conn->executeQuery($sql)->fetchOne();

        // Calculate enrolled students
        $sql = 'SELECT COUNT(*) as total FROM students WHERE registration_status = "Enrolled"';
        $enrolledCount = (int)$conn->executeQuery($sql)->fetchOne();

        $sql = 'SELECT COUNT(*) as total FROM students WHERE registration_status = "Not Enrolled"';
        $pendingCount = (int)$conn->executeQuery($sql)->fetchOne();

        $enrolledPercentage = $totalStudents > 0 ? round(($enrolledCount / $totalStudents) * 100) : 0;

        // Get liquidation statistics
        $liquidationStats = $liquidationRepository->getDashboardStatistics();

        // Get upcoming liquidations (next 30 days)
        $upcomingLiquidations = $liquidationRepository->findUpcoming(30);

        // Get overdue liquidations
        $overdueLiquidations = $liquidationRepository->findOverdue();

        // Get all campuses
        $campuses = $campusRepository->findAllActive();

        // Count active liquidations
        $activeLiquidations = count(array_filter($upcomingLiquidations, function ($liq) {
            return $liq['status'] !== 'Completed';
        }));

        // Get recent students for the directory table
        $sql = 'SELECT s.* 
                FROM students s 
                ORDER BY s.id DESC 
                LIMIT 4';
        $recentStudents = $conn->executeQuery($sql)->fetchAllAssociative();

        return $this->render('admin/dashboard.html.twig', [
            'campusStats' => $campusStats,
            'totalStudents' => $totalStudents,
            'enrolledCount' => $enrolledCount,
            'enrolledPercentage' => $enrolledPercentage,
            'pendingCount' => $pendingCount,
            'activeLiquidations' => $activeLiquidations,
            'liquidationStats' => $liquidationStats,
            'upcomingLiquidations' => $upcomingLiquidations,
            'overdueLiquidations' => $overdueLiquidations,
            'campuses' => $campuses,
            'recentStudents' => $recentStudents,
        ]);
    }

    #[Route('/admin/students', name: 'admin_students')]
    public function students(Request $request, StudentRepository $studentRepository): Response
    {
        // This will be handled by the new enhanced template with AJAX
        return $this->render('admin/students.html.twig');
    }

    #[Route('/admin/students/{id}', name: 'admin_student_view')]
    public function viewStudent(Student $student): Response
    {
        return $this->render('admin/student_view.html.twig', [
            'student' => $student,
        ]);
    }

    #[Route('/admin/students/{id}/update-status', name: 'admin_student_update_status', methods: ['POST'])]
    public function updateStatus(
        Student $student,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $status = $request->request->get('status');

        if (in_array($status, ['Pending', 'Approved', 'Rejected', 'On Hold'])) {
            $student->setRegistrationStatus($status);
            $entityManager->flush();

            $this->addFlash('success', 'Student status updated successfully.');
        }

        return $this->redirectToRoute('admin_student_view', ['id' => $student->getId()]);
    }

    #[Route('/admin/campuses', name: 'admin_campuses')]
    public function campuses(
        \App\Repository\CampusRepository $campusRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $conn = $entityManager->getConnection();

        // Get all campuses with student counts
        $sql = 'SELECT c.*, COUNT(s.id) as student_count 
                FROM campuses c 
                LEFT JOIN students s ON c.campus_name = s.campus 
                GROUP BY c.id 
                ORDER BY c.campus_name';
        $campuses = $conn->executeQuery($sql)->fetchAllAssociative();

        return $this->render('admin/campuses.html.twig', [
            'campuses' => $campuses,
        ]);
    }

    #[Route('/admin/liquidations', name: 'admin_liquidations')]
    public function liquidations(
        \App\Repository\LiquidationRepository $liquidationRepository
    ): Response {
        // Get all liquidations
        $upcomingLiquidations = $liquidationRepository->findUpcoming(365); // Next year
        $overdueLiquidations = $liquidationRepository->findOverdue();

        return $this->render('admin/liquidations.html.twig', [
            'upcomingLiquidations' => $upcomingLiquidations,
            'overdueLiquidations' => $overdueLiquidations,
        ]);
    }

    #[Route('/admin/reports', name: 'admin_reports')]
    public function reports(
        EntityManagerInterface $entityManager
    ): Response {
        $conn = $entityManager->getConnection();

        // Get various statistics for reports
        $sql = 'SELECT 
                    COUNT(*) as total_students,
                    SUM(CASE WHEN registration_status = "Enrolled" THEN 1 ELSE 0 END) as enrolled,
                    SUM(CASE WHEN registration_status = "Not Enrolled" THEN 1 ELSE 0 END) as not_enrolled,
                    SUM(CASE WHEN gender = "Male" THEN 1 ELSE 0 END) as male_students,
                    SUM(CASE WHEN gender = "Female" THEN 1 ELSE 0 END) as female_students
                FROM students';
        $stats = $conn->executeQuery($sql)->fetchAssociative();

        // Get scholarship distribution
        $sql = 'SELECT scholarship_type, COUNT(*) as count 
                FROM students 
                GROUP BY scholarship_type 
                ORDER BY count DESC';
        $scholarshipStats = $conn->executeQuery($sql)->fetchAllAssociative();

        // Get campus distribution
        $sql = 'SELECT c.campus_name, COUNT(s.id) as count 
                FROM campuses c 
                LEFT JOIN students s ON c.campus_name = s.campus 
                GROUP BY c.id, c.campus_name 
                ORDER BY count DESC';
        $campusStats = $conn->executeQuery($sql)->fetchAllAssociative();

        return $this->render('admin/reports.html.twig', [
            'stats' => $stats,
            'scholarshipStats' => $scholarshipStats,
            'campusStats' => $campusStats,
        ]);
    }

    #[Route('/admin/registration', name: 'admin_registration')]
    public function registration(
        \App\Repository\CampusRepository $campusRepository
    ): Response {
        $campuses = $campusRepository->findAllActive();

        return $this->render('admin/registration.html.twig', [
            'campuses' => $campuses,
        ]);
    }

    #[Route('/admin/profile', name: 'admin_profile')]
    public function profile(): Response
    {
        $admin = $this->getUser();

        if (!$admin) {
            return $this->redirectToRoute('admin_login');
        }

        return $this->render('admin/profile.html.twig', [
            'admin' => $admin,
        ]);
    }

    #[Route('/admin/profile/edit', name: 'admin_profile_edit', methods: ['GET', 'POST'])]
    public function profileEdit(
        Request $request,
        EntityManagerInterface $entityManager,
        \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $passwordHasher
    ): Response {
        $admin = $this->getUser();

        if (!$admin) {
            return $this->redirectToRoute('admin_login');
        }

        $error = null;
        $success = false;

        if ($request->isMethod('POST')) {
            $fullName = $request->request->get('full_name');
            $email = $request->request->get('email');
            $currentPassword = $request->request->get('current_password');
            $newPassword = $request->request->get('new_password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validate inputs
            if (empty($fullName) || empty($email)) {
                $error = 'Full name and email are required.';
            } else {
                // Check if email is being changed and if it's already taken by another admin
                if ($email !== $admin->getEmail()) {
                    $existingAdmin = $entityManager->getRepository(\App\Entity\Admin::class)
                        ->findOneBy(['email' => $email]);

                    if ($existingAdmin && $existingAdmin->getId() !== $admin->getId()) {
                        $error = 'This email is already in use by another administrator.';
                    }
                }

                if (!$error) {
                    // Update basic info
                    $admin->setFullName($fullName);
                    $admin->setEmail($email);

                    // Handle password change if requested
                    if (!empty($newPassword)) {
                        if (empty($currentPassword)) {
                            $error = 'Please enter your current password to change your password.';
                        } elseif (!$passwordHasher->isPasswordValid($admin, $currentPassword)) {
                            $error = 'Current password is incorrect.';
                        } elseif (strlen($newPassword) < 6) {
                            $error = 'New password must be at least 6 characters long.';
                        } elseif ($newPassword !== $confirmPassword) {
                            $error = 'New passwords do not match.';
                        } else {
                            $hashedPassword = $passwordHasher->hashPassword($admin, $newPassword);
                            $admin->setPassword($hashedPassword);
                        }
                    }

                    if (!$error) {
                        $entityManager->flush();
                        $success = true;
                        $this->addFlash('success', 'Profile updated successfully.');
                    }
                }
            }
        }

        return $this->render('admin/profile_edit.html.twig', [
            'admin' => $admin,
            'error' => $error,
            'success' => $success,
        ]);
    }
}
