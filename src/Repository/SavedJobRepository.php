<?php

namespace App\Repository;

use App\Entity\SavedJob;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavedJob>
 */
class SavedJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavedJob::class);
    }

    /**
     * Trouve toutes les offres sauvegardées d'un utilisateur
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('s.savedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une offre est sauvegardée par un utilisateur
     */
    public function isSavedByUser(int $publicationId, User $user): bool
    {
        $result = $this->createQueryBuilder('s')
            ->andWhere('s.publication = :publicationId')
            ->andWhere('s.user = :user')
            ->setParameter('publicationId', $publicationId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}