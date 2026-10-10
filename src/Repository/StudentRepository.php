<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * Find students by campus
     */
    public function findByCampus(string $campus): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.campus = :campus')
            ->setParameter('campus', $campus)
            ->orderBy('s.lastName', 'ASC')
            ->getResult();
    }

    /**
     * Find students by year level
     */
    public function findByYearLevel(string $yearLevel): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.yearLevel = :yearLevel')
            ->setParameter('yearLevel', $yearLevel)
            ->orderBy('s.lastName', 'ASC')
            ->getResult();
    }

    /**
     * Find students by scholarship type
     */
    public function findByScholarshipType(string $scholarshipType): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.scholarshipType = :scholarshipType')
            ->setParameter('scholarshipType', $scholarshipType)
            ->orderBy('s.lastName', 'ASC')
            ->getResult();
    }

    /**
     * Search students by name or student number
     */
    public function searchStudents(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.studentNumber LIKE :query OR s.firstName LIKE :query OR s.lastName LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('s.lastName', 'ASC')
            ->getResult();
    }

    /**
     * Get campus statistics
     */
    public function getCampusStatistics(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.campus, COUNT(s.id) as total')
            ->groupBy('s.campus')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['campus']] = $row['total'];
        }

        return $stats;
    }

    /**
     * Get scholarship type statistics
     */
    public function getScholarshipTypeStatistics(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.scholarshipType, COUNT(s.id) as total')
            ->groupBy('s.scholarshipType')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['scholarshipType']] = $row['total'];
        }

        return $stats;
    }

    /**
     * Get year level statistics
     */
    public function getYearLevelStatistics(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.yearLevel, COUNT(s.id) as total')
            ->groupBy('s.yearLevel')
            ->orderBy('s.yearLevel', 'ASC')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['yearLevel']] = $row['total'];
        }

        return $stats;
    }

    /**
     * Get registration status statistics
     */
    public function getRegistrationStatusStatistics(): array
    {
        $result = $this->createQueryBuilder('s')
            ->select('s.registrationStatus, COUNT(s.id) as total')
            ->groupBy('s.registrationStatus')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['registrationStatus']] = $row['total'];
        }

        return $stats;
    }
}
