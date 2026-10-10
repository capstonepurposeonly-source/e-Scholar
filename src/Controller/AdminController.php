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

        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/admin/logout', name: 'admin_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(StudentRepository $studentRepository): Response
    {
        // Get statistics
        $campusStats = $studentRepository->getCampusStatistics();
        $scholarshipStats = $studentRepository->getScholarshipTypeStatistics();
        $yearLevelStats = $studentRepository->getYearLevelStatistics();
        $statusStats = $studentRepository->getRegistrationStatusStatistics();

        // Get total students
        $totalStudents = array_sum($campusStats);

        return $this->render('admin/dashboard.html.twig', [
            'campusStats' => $campusStats,
            'scholarshipStats' => $scholarshipStats,
            'yearLevelStats' => $yearLevelStats,
            'statusStats' => $statusStats,
            'totalStudents' => $totalStudents,
        ]);
    }

    #[Route('/admin/students', name: 'admin_students')]
    public function students(Request $request, StudentRepository $studentRepository): Response
    {
        $search = $request->query->get('search', '');
        $campus = $request->query->get('campus', '');
        $yearLevel = $request->query->get('year_level', '');
        $scholarshipType = $request->query->get('scholarship_type', '');
        $status = $request->query->get('status', '');

        // Build query
        $queryBuilder = $studentRepository->createQueryBuilder('s');

        if ($search) {
            $queryBuilder->andWhere('s.studentNumber LIKE :search OR s.firstName LIKE :search OR s.lastName LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($campus) {
            $queryBuilder->andWhere('s.campus = :campus')
                ->setParameter('campus', $campus);
        }

        if ($yearLevel) {
            $queryBuilder->andWhere('s.yearLevel = :yearLevel')
                ->setParameter('yearLevel', $yearLevel);
        }

        if ($scholarshipType) {
            $queryBuilder->andWhere('s.scholarshipType = :scholarshipType')
                ->setParameter('scholarshipType', $scholarshipType);
        }

        if ($status) {
            $queryBuilder->andWhere('s.registrationStatus = :status')
                ->setParameter('status', $status);
        }

        $students = $queryBuilder->orderBy('s.createdAt', 'DESC')->getQuery()->getResult();

        return $this->render('admin/students.html.twig', [
            'students' => $students,
            'search' => $search,
            'campus' => $campus,
            'yearLevel' => $yearLevel,
            'scholarshipType' => $scholarshipType,
            'status' => $status,
        ]);
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
}
