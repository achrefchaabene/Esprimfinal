<?php

namespace App\Repository;

use App\Entity\Interview;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Interview>
 */
class InterviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Interview::class);
    }

    public function findUpcomingForUser(User $user): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.application', 'a')
            ->andWhere('a.user = :user')
            ->andWhere('i.status = :status')
            ->andWhere('i.scheduledAt > :now')
            ->setParameter('user', $user)
            ->setParameter('status', 'scheduled')
            ->setParameter('now', new \DateTime())
            ->orderBy('i.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(Interview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Interview $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}


