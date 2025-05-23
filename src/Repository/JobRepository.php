<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Job>
 */
class JobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Job::class);
    }

    public function save(Job $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Job $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveJobs(): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', Job::STATUS_ACTIVE)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompanyJobs(User $company): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.company = :company')
            ->setParameter('company', $company)
            ->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function searchJobs(?string $keywords, ?string $location): array
    {
        $query = $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', Job::STATUS_ACTIVE);

        if ($keywords) {
            $query->andWhere('j.title LIKE :keywords OR j.description LIKE :keywords')
                ->setParameter('keywords', '%'.$keywords.'%');
        }

        if ($location) {
            $query->andWhere('j.location LIKE :location')
                ->setParameter('location', '%'.$location.'%');
        }

        return $query->orderBy('j.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentJobs(int $limit = 10): array
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.status = :status')
            ->setParameter('status', Job::STATUS_ACTIVE)
            ->orderBy('j.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}