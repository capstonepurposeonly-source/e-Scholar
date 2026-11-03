<?php

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announcement>
 */
class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    /**
     * Get active announcements for students
     */
    public function findActiveAnnouncements(): array
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('a')
            ->andWhere('a.isActive = :active')
            ->andWhere('a.publishDate <= :now')
            ->andWhere('a.expiryDate IS NULL OR a.expiryDate >= :now')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->orderBy('a.publishDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent announcements (limit)
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
