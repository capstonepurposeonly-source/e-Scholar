<?php

namespace App\Controller\Api;

use App\Repository\StudentRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/students')]
class StudentApiController extends AbstractController
{
    #[Route('', name: 'api_students_list', methods: ['GET'])]
    public function list(
        Request $request,
        StudentRepository $studentRepository,
        Connection $connection
    ): JsonResponse {
        // Get filter parameters
        $search = $request->query->get('search', '');
        $campus = $request->query->get('campus', '');
        $status = $request->query->get('status', '');
        $scholarshipType = $request->query->get('scholarship_type', '');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(10, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        // Build query
        $qb = $studentRepository->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true);

        // Apply search filter
        if ($search) {
            $qb->andWhere('s.studentNumber LIKE :search OR s.lrn LIKE :search OR s.firstName LIKE :search OR s.lastName LIKE :search OR s.email LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        // Apply campus filter
        if ($campus) {
            $qb->andWhere('s.campus = :campus')
                ->setParameter('campus', $campus);
        }

        // Apply status filter
        if ($status) {
            $qb->andWhere('s.registrationStatus = :status')
                ->setParameter('status', $status);
        }

        // Apply scholarship type filter
        if ($scholarshipType) {
            $qb->andWhere('s.scholarshipType = :scholarshipType')
                ->setParameter('scholarshipType', $scholarshipType);
        }

        // Get total count
        $totalQuery = clone $qb;
        $total = (int) $totalQuery->select('COUNT(s.id)')->getQuery()->getSingleScalarResult();

        // Get paginated results
        $students = $qb
            ->orderBy('s.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Format response
        $data = array_map(function($student) {
            return [
                'id' => $student->getId(),
                'student_number' => $student->getStudentNumber(),
                'lrn' => $student->getLrn(),
                'full_name' => trim($student->getFirstName() . ' ' . ($student->getMiddleName() ?? '') . ' ' . $student->getLastName() . ' ' . ($student->getExtensionName() ?? '')),
                'first_name' => $student->getFirstName(),
                'middle_name' => $student->getMiddleName(),
                'last_name' => $student->getLastName(),
                'email' => $student->getEmail(),
                'campus' => $student->getCampus(),
                'course' => $student->getCourse(),
                'year_level' => $student->getYearLevel(),
                'scholarship_type' => $student->getScholarshipType(),
                'registration_status' => $student->getRegistrationStatus(),
                'photo_url' => $student->getProfilePicturePath(),
                'contact_number' => $student->getContactNumber(),
                'created_at' => $student->getCreatedAt()?->format('Y-m-d H:i:s'),
            ];
        }, $students);

        return $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => (int) ceil($total / $limit),
            ],
            'filters' => [
                'search' => $search,
                'campus' => $campus,
                'status' => $status,
                'scholarship_type' => $scholarshipType,
            ],
        ]);
    }

    #[Route('/{id}', name: 'api_student_detail', methods: ['GET'])]
    public function detail(int $id, StudentRepository $studentRepository, Connection $connection): JsonResponse
    {
        $student = $studentRepository->find($id);

        if (!$student) {
            return $this->json([
                'success' => false,
                'error' => 'Student not found',
            ], 404);
        }

        // Get address
        $address = $connection->fetchAssociative(
            'SELECT * FROM student_addresses WHERE student_id = ? LIMIT 1',
            [$id]
        );

        // Get family info
        $family = $connection->fetchAssociative(
            'SELECT * FROM student_family WHERE student_id = ? LIMIT 1',
            [$id]
        );

        // Get liquidations
        $liquidations = $connection->fetchAllAssociative(
            'SELECT * FROM liquidations WHERE student_id = ? ORDER BY deadline_date DESC',
            [$id]
        );

        return $this->json([
            'success' => true,
            'data' => [
                'student' => [
                    'id' => $student->getId(),
                    'student_number' => $student->getStudentNumber(),
                    'lrn' => $student->getLrn(),
                    'scholarship_type' => $student->getScholarshipType(),
                    'first_name' => $student->getFirstName(),
                    'middle_name' => $student->getMiddleName(),
                    'last_name' => $student->getLastName(),
                    'extension_name' => $student->getExtensionName(),
                    'full_name' => trim($student->getFirstName() . ' ' . ($student->getMiddleName() ?? '') . ' ' . $student->getLastName() . ' ' . ($student->getExtensionName() ?? '')),
                    'date_of_birth' => $student->getDateOfBirth()?->format('Y-m-d'),
                    'place_of_birth' => $student->getPlaceOfBirth(),
                    'sex' => $student->getSex(),
                    'civil_status' => $student->getCivilStatus(),
                    'religion' => $student->getReligion(),
                    'campus' => $student->getCampus(),
                    'course' => $student->getCourse(),
                    'year_level' => $student->getYearLevel(),
                    'gwa' => $student->getGwa(),
                    'semester' => $student->getSemester(),
                    'academic_year' => $student->getAcademicYear(),
                    'high_school' => $student->getHighSchool(),
                    'high_school_year' => $student->getHighSchoolYear(),
                    'email' => $student->getEmail(),
                    'contact_number' => $student->getContactNumber(),
                    'alternate_contact_number' => $student->getAlternateContactNumber(),
                    'permanent_address' => $student->getPermanentAddress(),
                    'current_address' => $student->getCurrentAddress(),
                    'barangay' => $student->getBarangay(),
                    'municipality' => $student->getMunicipality(),
                    'province' => $student->getProvince(),
                    'zip_code' => $student->getZipCode(),
                    'registration_status' => $student->getRegistrationStatus(),
                    'profile_picture_path' => $student->getProfilePicturePath(),
                    'created_at' => $student->getCreatedAt()?->format('Y-m-d H:i:s'),
                ],
                'address' => $address ?: null,
                'family' => $family ? [
                    'father_name' => $family['father_name'] ?? $student->getFatherName(),
                    'father_occupation' => $family['father_occupation'] ?? $student->getFatherOccupation(),
                    'father_contact_number' => $family['father_contact'] ?? $student->getFatherContactNumber(),
                    'mother_name' => $family['mother_name'] ?? $student->getMotherName(),
                    'mother_occupation' => $family['mother_occupation'] ?? $student->getMotherOccupation(),
                    'mother_contact_number' => $family['mother_contact'] ?? $student->getMotherContactNumber(),
                    'guardian_name' => $family['guardian_name'] ?? $student->getGuardianName(),
                    'guardian_relationship' => $family['guardian_relationship'] ?? $student->getGuardianRelationship(),
                    'guardian_contact_number' => $family['guardian_contact'] ?? $student->getGuardianContactNumber(),
                    'annual_income' => $family['annual_income'] ?? $student->getFamilyMonthlyIncome(),
                    'number_of_siblings' => $student->getNumberOfSiblings(),
                ] : [
                    'father_name' => $student->getFatherName(),
                    'father_occupation' => $student->getFatherOccupation(),
                    'father_contact_number' => $student->getFatherContactNumber(),
                    'mother_name' => $student->getMotherName(),
                    'mother_occupation' => $student->getMotherOccupation(),
                    'mother_contact_number' => $student->getMotherContactNumber(),
                    'guardian_name' => $student->getGuardianName(),
                    'guardian_relationship' => $student->getGuardianRelationship(),
                    'guardian_contact_number' => $student->getGuardianContactNumber(),
                    'annual_income' => $student->getFamilyMonthlyIncome(),
                    'number_of_siblings' => $student->getNumberOfSiblings(),
                ],
                'liquidations' => $liquidations,
            ],
        ]);
    }

    #[Route('/filters/options', name: 'api_students_filter_options', methods: ['GET'])]
    public function filterOptions(Connection $connection): JsonResponse
    {
        // Get available campuses
        $campuses = $connection->fetchFirstColumn(
            'SELECT DISTINCT campus FROM students WHERE is_active = 1 ORDER BY campus'
        );

        // Get available scholarship types
        $scholarshipTypes = $connection->fetchFirstColumn(
            'SELECT DISTINCT scholarship_type FROM students WHERE is_active = 1 ORDER BY scholarship_type'
        );

        return $this->json([
            'success' => true,
            'data' => [
                'campuses' => $campuses,
                'scholarship_types' => $scholarshipTypes,
                'statuses' => ['Enrolled', 'Not Enrolled'],
            ],
        ]);
    }
}
