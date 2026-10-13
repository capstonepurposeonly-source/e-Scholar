<?php

namespace App\Controller\Api;

use App\Repository\CampusRepository;
use App\Repository\LiquidationRepository;
use App\Repository\StudentRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/dashboard')]
class DashboardApiController extends AbstractController
{
    #[Route('/overview', name: 'api_dashboard_overview', methods: ['GET'])]
    public function overview(
        StudentRepository $studentRepository,
        CampusRepository $campusRepository,
        LiquidationRepository $liquidationRepository,
        Connection $connection
    ): JsonResponse {
        // Get total student count
        $totalStudents = $connection->fetchOne('SELECT COUNT(*) FROM students WHERE is_active = 1');

        // Get student count per campus
        $campusStats = $connection->fetchAllAssociative(
            'SELECT c.campus_name, c.campus_code, COUNT(s.id) as student_count 
             FROM campuses c 
             LEFT JOIN students s ON c.id = s.campus_id AND s.is_active = 1
             WHERE c.is_active = 1
             GROUP BY c.id, c.campus_name, c.campus_code
             ORDER BY c.campus_name'
        );

        // Get liquidation statistics
        $liquidationStats = $liquidationRepository->getDashboardStatistics();

        // Get upcoming liquidations (next 30 days)
        $upcomingLiquidations = $liquidationRepository->findUpcoming(30);
        
        $liquidationData = array_map(function($liq) {
            return [
                'id' => $liq->getId(),
                'student_name' => $liq->getStudent()->getFirstName() . ' ' . $liq->getStudent()->getLastName(),
                'student_number' => $liq->getStudent()->getStudentNumber(),
                'scholarship_type' => $liq->getScholarshipType(),
                'academic_year' => $liq->getAcademicYear(),
                'semester' => $liq->getSemester(),
                'deadline_date' => $liq->getDeadlineDate()->format('Y-m-d'),
                'deadline_formatted' => $liq->getDeadlineDate()->format('M d, Y'),
                'days_until_deadline' => $liq->getDaysUntilDeadline(),
                'status' => $liq->getStatus(),
                'deadline_status' => $liq->getDeadlineStatus(),
                'amount' => $liq->getAmount(),
                'campus' => $liq->getStudent()->getCampus(),
            ];
        }, $upcomingLiquidations);

        // Get registration status distribution
        $registrationStats = $studentRepository->getRegistrationStatusStatistics();

        // Get scholarship type distribution
        $scholarshipStats = $studentRepository->getScholarshipTypeStatistics();

        return $this->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_students' => (int) $totalStudents,
                    'enrolled_students' => $registrationStats['Enrolled'] ?? 0,
                    'not_enrolled_students' => $registrationStats['Not Enrolled'] ?? 0,
                ],
                'campus_distribution' => $campusStats,
                'liquidations' => [
                    'statistics' => $liquidationStats,
                    'upcoming' => $liquidationData,
                    'overdue_count' => $liquidationStats['overdue'],
                    'due_soon_count' => $liquidationStats['dueSoon'],
                ],
                'scholarship_distribution' => $scholarshipStats,
                'registration_status' => $registrationStats,
            ],
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/campus-stats', name: 'api_dashboard_campus_stats', methods: ['GET'])]
    public function campusStats(Connection $connection): JsonResponse
    {
        $stats = $connection->fetchAllAssociative(
            'SELECT 
                c.campus_name, 
                c.campus_code,
                COUNT(s.id) as total_students,
                SUM(CASE WHEN s.registration_status = "Enrolled" THEN 1 ELSE 0 END) as enrolled,
                SUM(CASE WHEN s.registration_status = "Not Enrolled" THEN 1 ELSE 0 END) as not_enrolled
             FROM campuses c 
             LEFT JOIN students s ON c.id = s.campus_id AND s.is_active = 1
             WHERE c.is_active = 1
             GROUP BY c.id, c.campus_name, c.campus_code
             ORDER BY total_students DESC'
        );

        return $this->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    #[Route('/liquidations/upcoming', name: 'api_dashboard_liquidations_upcoming', methods: ['GET'])]
    public function upcomingLiquidations(
        Request $request,
        LiquidationRepository $liquidationRepository
    ): JsonResponse {
        $days = $request->query->getInt('days', 30);
        $liquidations = $liquidationRepository->findUpcoming($days);

        $data = array_map(function($liq) {
            return [
                'id' => $liq->getId(),
                'student' => [
                    'id' => $liq->getStudent()->getId(),
                    'name' => $liq->getStudent()->getFirstName() . ' ' . $liq->getStudent()->getLastName(),
                    'student_number' => $liq->getStudent()->getStudentNumber(),
                    'campus' => $liq->getStudent()->getCampus(),
                    'email' => $liq->getStudent()->getEmail(),
                ],
                'scholarship_type' => $liq->getScholarshipType(),
                'academic_year' => $liq->getAcademicYear(),
                'semester' => $liq->getSemester(),
                'deadline_date' => $liq->getDeadlineDate()->format('Y-m-d'),
                'deadline_formatted' => $liq->getDeadlineDate()->format('F d, Y'),
                'days_until_deadline' => $liq->getDaysUntilDeadline(),
                'status' => $liq->getStatus(),
                'deadline_status' => $liq->getDeadlineStatus(),
                'amount' => $liq->getAmount(),
                'submission_date' => $liq->getSubmissionDate()?->format('Y-m-d H:i:s'),
            ];
        }, $liquidations);

        return $this->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    #[Route('/liquidations/overdue', name: 'api_dashboard_liquidations_overdue', methods: ['GET'])]
    public function overdueLiquidations(LiquidationRepository $liquidationRepository): JsonResponse
    {
        $liquidations = $liquidationRepository->findOverdue();

        $data = array_map(function($liq) {
            return [
                'id' => $liq->getId(),
                'student' => [
                    'id' => $liq->getStudent()->getId(),
                    'name' => $liq->getStudent()->getFirstName() . ' ' . $liq->getStudent()->getLastName(),
                    'student_number' => $liq->getStudent()->getStudentNumber(),
                    'campus' => $liq->getStudent()->getCampus(),
                    'email' => $liq->getStudent()->getEmail(),
                    'contact_number' => $liq->getStudent()->getContactNumber(),
                ],
                'scholarship_type' => $liq->getScholarshipType(),
                'academic_year' => $liq->getAcademicYear(),
                'semester' => $liq->getSemester(),
                'deadline_date' => $liq->getDeadlineDate()->format('Y-m-d'),
                'days_overdue' => abs($liq->getDaysUntilDeadline()),
                'amount' => $liq->getAmount(),
            ];
        }, $liquidations);

        return $this->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    #[Route('/liquidations/status/{status}', name: 'api_dashboard_liquidations_by_status', methods: ['GET'])]
    public function liquidationsByStatus(
        string $status,
        LiquidationRepository $liquidationRepository
    ): JsonResponse {
        $validStatuses = ['Pending', 'Submitted', 'Approved', 'Rejected', 'Overdue'];
        
        if (!in_array($status, $validStatuses)) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid status. Valid statuses: ' . implode(', ', $validStatuses),
            ], 400);
        }

        $liquidations = $liquidationRepository->findByStatus($status);

        $data = array_map(function($liq) {
            return [
                'id' => $liq->getId(),
                'student_name' => $liq->getStudent()->getFirstName() . ' ' . $liq->getStudent()->getLastName(),
                'student_number' => $liq->getStudent()->getStudentNumber(),
                'scholarship_type' => $liq->getScholarshipType(),
                'deadline_date' => $liq->getDeadlineDate()->format('Y-m-d'),
                'status' => $liq->getStatus(),
                'amount' => $liq->getAmount(),
            ];
        }, $liquidations);

        return $this->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    #[Route('/statistics/summary', name: 'api_dashboard_statistics_summary', methods: ['GET'])]
    public function statisticsSummary(
        StudentRepository $studentRepository,
        LiquidationRepository $liquidationRepository,
        Connection $connection
    ): JsonResponse {
        $totalStudents = $connection->fetchOne('SELECT COUNT(*) FROM students WHERE is_active = 1');
        $liquidationStats = $liquidationRepository->getDashboardStatistics();
        $campusCount = $connection->fetchOne('SELECT COUNT(*) FROM campuses WHERE is_active = 1');
        
        // Get scholarship programs count
        $scholarshipCount = $connection->fetchOne('SELECT COUNT(DISTINCT scholarship_type) FROM students WHERE is_active = 1');

        return $this->json([
            'success' => true,
            'data' => [
                'total_students' => (int) $totalStudents,
                'total_campuses' => (int) $campusCount,
                'total_scholarship_programs' => (int) $scholarshipCount,
                'liquidations' => [
                    'total' => $liquidationStats['total'],
                    'pending' => $liquidationStats['pending'],
                    'overdue' => $liquidationStats['overdue'],
                    'due_soon' => $liquidationStats['dueSoon'],
                    'submitted' => $liquidationStats['submitted'],
                    'approved' => $liquidationStats['approved'],
                ],
            ],
        ]);
    }
}
