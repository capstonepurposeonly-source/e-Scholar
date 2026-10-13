<?php

namespace App\Controller\Api;

use App\Entity\Liquidation;
use App\Repository\LiquidationRepository;
use App\Repository\StudentRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/liquidations')]
class LiquidationApiController extends AbstractController
{
    #[Route('/create', name: 'api_liquidation_create', methods: ['POST'])]
    public function create(
        Request $request,
        StudentRepository $studentRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validate required fields
        if (!isset($data['student_id'], $data['scholarship_type'], $data['deadline_date'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing required fields: student_id, scholarship_type, deadline_date'
            ], 400);
        }

        $student = $studentRepo->find($data['student_id']);
        if (!$student) {
            return $this->json([
                'success' => false,
                'error' => 'Student not found'
            ], 404);
        }

        try {
            $liquidation = new Liquidation();
            $liquidation->setStudent($student);
            $liquidation->setScholarshipType($data['scholarship_type']);
            $liquidation->setAcademicYear($data['academic_year'] ?? '2024-2025');
            $liquidation->setSemester($data['semester'] ?? '1st Semester');
            $liquidation->setDeadlineDate(new \DateTime($data['deadline_date']));
            $liquidation->setAmount($data['amount'] ?? null);
            $liquidation->setRemarks($data['remarks'] ?? null);
            $liquidation->setStatus('Pending');

            $em->persist($liquidation);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Liquidation deadline created successfully',
                'data' => [
                    'id' => $liquidation->getId(),
                    'student_name' => $student->getFirstName() . ' ' . $student->getLastName(),
                    'student_number' => $student->getStudentNumber(),
                    'scholarship_type' => $liquidation->getScholarshipType(),
                    'deadline_date' => $liquidation->getDeadlineDate()->format('Y-m-d'),
                    'days_until_deadline' => $liquidation->getDaysUntilDeadline(),
                    'status' => $liquidation->getStatus(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to create liquidation: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('', name: 'api_liquidations_list', methods: ['GET'])]
    public function list(Request $request, Connection $connection): JsonResponse
    {
        $campus = $request->query->get('campus', '');
        $status = $request->query->get('status', '');
        $scholarshipType = $request->query->get('scholarship_type', '');

        $sql = 'SELECT l.*, 
                s.first_name, s.last_name, s.student_number, s.campus, s.email, s.contact_number,
                DATEDIFF(l.deadline_date, CURDATE()) as days_until_deadline
                FROM liquidations l 
                JOIN students s ON l.student_id = s.id 
                WHERE 1=1';
        
        $params = [];
        
        if ($campus) {
            $sql .= ' AND s.campus = ?';
            $params[] = $campus;
        }
        
        if ($status) {
            $sql .= ' AND l.status = ?';
            $params[] = $status;
        }

        if ($scholarshipType) {
            $sql .= ' AND l.scholarship_type = ?';
            $params[] = $scholarshipType;
        }

        $sql .= ' ORDER BY l.deadline_date ASC';

        $liquidations = $connection->fetchAllAssociative($sql, $params);

        // Add deadline status for each liquidation
        foreach ($liquidations as &$liq) {
            $daysUntil = (int) $liq['days_until_deadline'];
            
            if ($liq['status'] === 'Pending') {
                if ($daysUntil < 0) {
                    $liq['deadline_status'] = 'Overdue';
                } elseif ($daysUntil <= 3) {
                    $liq['deadline_status'] = 'Due Soon';
                } else {
                    $liq['deadline_status'] = 'Active';
                }
            } else {
                $liq['deadline_status'] = $liq['status'];
            }
        }

        return $this->json([
            'success' => true,
            'data' => $liquidations,
            'count' => count($liquidations)
        ]);
    }

    #[Route('/{id}', name: 'api_liquidation_detail', methods: ['GET'])]
    public function detail(int $id, LiquidationRepository $repo): JsonResponse
    {
        $liquidation = $repo->find($id);
        
        if (!$liquidation) {
            return $this->json([
                'success' => false,
                'error' => 'Liquidation not found'
            ], 404);
        }

        $student = $liquidation->getStudent();

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $liquidation->getId(),
                'student' => [
                    'id' => $student->getId(),
                    'name' => $student->getFirstName() . ' ' . $student->getLastName(),
                    'student_number' => $student->getStudentNumber(),
                    'campus' => $student->getCampus(),
                    'email' => $student->getEmail(),
                    'contact_number' => $student->getContactNumber(),
                ],
                'scholarship_type' => $liquidation->getScholarshipType(),
                'academic_year' => $liquidation->getAcademicYear(),
                'semester' => $liquidation->getSemester(),
                'deadline_date' => $liquidation->getDeadlineDate()->format('Y-m-d'),
                'submission_date' => $liquidation->getSubmissionDate()?->format('Y-m-d H:i:s'),
                'status' => $liquidation->getStatus(),
                'amount' => $liquidation->getAmount(),
                'remarks' => $liquidation->getRemarks(),
                'documents_url' => $liquidation->getDocumentsUrl(),
                'approved_at' => $liquidation->getApprovedAt()?->format('Y-m-d H:i:s'),
                'rejection_reason' => $liquidation->getRejectionReason(),
                'days_until_deadline' => $liquidation->getDaysUntilDeadline(),
                'deadline_status' => $liquidation->getDeadlineStatus(),
                'created_at' => $liquidation->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/{id}/update-status', name: 'api_liquidation_update_status', methods: ['PATCH'])]
    public function updateStatus(
        int $id,
        Request $request,
        LiquidationRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $liquidation = $repo->find($id);
        
        if (!$liquidation) {
            return $this->json([
                'success' => false,
                'error' => 'Liquidation not found'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['status'])) {
            $validStatuses = ['Pending', 'Submitted', 'Approved', 'Rejected', 'Overdue'];
            if (!in_array($data['status'], $validStatuses)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid status. Valid values: ' . implode(', ', $validStatuses)
                ], 400);
            }
            
            $liquidation->setStatus($data['status']);
            
            if ($data['status'] === 'Submitted' && !$liquidation->getSubmissionDate()) {
                $liquidation->setSubmissionDate(new \DateTime());
            }
            
            if ($data['status'] === 'Approved') {
                $liquidation->setApprovedAt(new \DateTime());
                if ($this->getUser()) {
                    $liquidation->setApprovedBy($this->getUser()->getId());
                }
            }
            
            if ($data['status'] === 'Rejected' && isset($data['rejection_reason'])) {
                $liquidation->setRejectionReason($data['rejection_reason']);
            }
        }

        if (isset($data['remarks'])) {
            $liquidation->setRemarks($data['remarks']);
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Liquidation status updated successfully',
            'data' => [
                'id' => $liquidation->getId(),
                'status' => $liquidation->getStatus(),
                'submission_date' => $liquidation->getSubmissionDate()?->format('Y-m-d H:i:s'),
                'approved_at' => $liquidation->getApprovedAt()?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_liquidation_delete', methods: ['DELETE'])]
    public function delete(int $id, LiquidationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $liquidation = $repo->find($id);
        
        if (!$liquidation) {
            return $this->json([
                'success' => false,
                'error' => 'Liquidation not found'
            ], 404);
        }

        $em->remove($liquidation);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Liquidation deleted successfully'
        ]);
    }

    #[Route('/bulk-create', name: 'api_liquidation_bulk_create', methods: ['POST'])]
    public function bulkCreate(
        Request $request,
        StudentRepository $studentRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['student_ids'], $data['scholarship_type'], $data['deadline_date'])) {
            return $this->json([
                'success' => false,
                'error' => 'Missing required fields'
            ], 400);
        }

        $created = 0;
        $errors = [];

        foreach ($data['student_ids'] as $studentId) {
            try {
                $student = $studentRepo->find($studentId);
                if (!$student) {
                    $errors[] = "Student ID {$studentId} not found";
                    continue;
                }

                $liquidation = new Liquidation();
                $liquidation->setStudent($student);
                $liquidation->setScholarshipType($data['scholarship_type']);
                $liquidation->setAcademicYear($data['academic_year'] ?? '2024-2025');
                $liquidation->setSemester($data['semester'] ?? '1st Semester');
                $liquidation->setDeadlineDate(new \DateTime($data['deadline_date']));
                $liquidation->setAmount($data['amount'] ?? null);
                $liquidation->setRemarks($data['remarks'] ?? null);
                $liquidation->setStatus('Pending');

                $em->persist($liquidation);
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Student ID {$studentId}: " . $e->getMessage();
            }
        }

        $em->flush();

        return $this->json([
            'success' => true,
            'message' => "Created {$created} liquidation(s)",
            'created' => $created,
            'errors' => $errors
        ]);
    }
}
