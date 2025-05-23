<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * Trouve toutes les candidatures d'un utilisateur
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les candidatures pour une publication
     */
    public function findByPublication($publicationId)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.publication = :publicationId')
            ->setParameter('publicationId', $publicationId)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les candidatures par statut pour un utilisateur
     */
    public function countByStatus(User $user)
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.status')
            ->getQuery()
            ->getResult();

        $counts = [
            'pending' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'total' => 0
        ];

        foreach ($result as $row) {
            $counts[$row['status']] = $row['count'];
            $counts['total'] += $row['count'];
        }

        return $counts;
    }
}