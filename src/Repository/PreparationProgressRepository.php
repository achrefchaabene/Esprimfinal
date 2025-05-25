<?php

namespace App\Repository;

use App\Entity\PreparationProgress;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PreparationProgress>
 */
class PreparationProgressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreparationProgress::class);
    }

    public function save(PreparationProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PreparationProgress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOrCreateForUser(User $user): PreparationProgress
    {
        $progress = $this->findOneBy(['user' => $user]);
        
        if (!$progress) {
            $progress = new PreparationProgress();
            $progress->setUser($user);
            $this->save($progress, true);
        }
        
        return $progress;
    }
}
