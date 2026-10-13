<?php

namespace App\Repository;

use App\Entity\Liquidation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Liquidation>
 */
class LiquidationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Liquidation::class);
    }

    /**
     * Get upcoming liquidations (within next 30 days)
     */
    public function findUpcoming(int $days = 30): array
    {
        $now = new \DateTime('today');
        $future = new \DateTime("+{$days} days");

        return $this->createQueryBuilder('l')
            ->join('l.student', 's')
            ->addSelect('s')
            ->andWhere('l.deadlineDate BETWEEN :now AND :future')
            ->andWhere('l.status IN (:statuses)')
            ->setParameter('now', $now)
            ->setParameter('future', $future)
            ->setParameter('statuses', ['Pending', 'Submitted'])
            ->orderBy('l.deadlineDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get overdue liquidations
     */
    public function findOverdue(): array
    {
        $now = new \DateTime('today');

        return $this->createQueryBuilder('l')
            ->join('l.student', 's')
            ->addSelect('s')
            ->andWhere('l.deadlineDate < :now')
            ->andWhere('l.status = :status')
            ->setParameter('now', $now)
            ->setParameter('status', 'Pending')
            ->orderBy('l.deadlineDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get liquidations by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.student', 's')
            ->addSelect('s')
            ->andWhere('l.status = :status')
            ->setParameter('status', $status)
            ->orderBy('l.deadlineDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get liquidation statistics by status
     */
    public function getStatusStatistics(): array
    {
        $result = $this->createQueryBuilder('l')
            ->select('l.status, COUNT(l.id) as total')
            ->groupBy('l.status')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($result as $row) {
            $stats[$row['status']] = $row['total'];
        }

        return $stats;
    }

    /**
     * Get liquidations due within specified days
     */
    public function findDueWithinDays(int $days = 7): array
    {
        $now = new \DateTime('today');
        $future = new \DateTime("+{$days} days");

        return $this->createQueryBuilder('l')
            ->join('l.student', 's')
            ->addSelect('s')
            ->andWhere('l.deadlineDate BETWEEN :now AND :future')
            ->andWhere('l.status = :status')
            ->setParameter('now', $now)
            ->setParameter('future', $future)
            ->setParameter('status', 'Pending')
            ->orderBy('l.deadlineDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get liquidations for specific student
     */
    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('l.deadlineDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get liquidation statistics for dashboard
     */
    public function getDashboardStatistics(): array
    {
        $now = new \DateTime('today');
        $sevenDays = new \DateTime('+7 days');
        
        $qb = $this->createQueryBuilder('l');
        
        return [
            'total' => (int) $qb->select('COUNT(l.id)')->getQuery()->getSingleScalarResult(),
            'pending' => (int) $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.status = :status')
                ->setParameter('status', 'Pending')
                ->getQuery()
                ->getSingleScalarResult(),
            'overdue' => (int) $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.status = :status AND l.deadlineDate < :now')
                ->orWhere('l.status = :overdueStatus')
                ->setParameter('status', 'Pending')
                ->setParameter('overdueStatus', 'Overdue')
                ->setParameter('now', $now)
                ->getQuery()
                ->getSingleScalarResult(),
            'dueSoon' => (int) $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.status = :status')
                ->andWhere('l.deadlineDate BETWEEN :now AND :sevenDays')
                ->setParameter('status', 'Pending')
                ->setParameter('now', $now)
                ->setParameter('sevenDays', $sevenDays)
                ->getQuery()
                ->getSingleScalarResult(),
            'submitted' => (int) $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.status = :status')
                ->setParameter('status', 'Submitted')
                ->getQuery()
                ->getSingleScalarResult(),
            'approved' => (int) $this->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.status = :status')
                ->setParameter('status', 'Approved')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }
}
