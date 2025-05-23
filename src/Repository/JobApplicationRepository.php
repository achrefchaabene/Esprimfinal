<?php

namespace App\Repository;

use App\Entity\Job;
use App\Entity\JobApplication;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<JobApplication>
 */
class JobApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobApplication::class);
    }

    public function save(JobApplication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(JobApplication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findApplication(User $user, Job $job): ?JobApplication
    {
        return $this->createQueryBuilder('ja')
            ->andWhere('ja.user = :user')
            ->andWhere('ja.job = :job')
            ->setParameter('user', $user)
            ->setParameter('job', $job)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findUserApplications(User $user): array
    {
        return $this->createQueryBuilder('ja')
            ->leftJoin('ja.job', 'j')
            ->addSelect('j')
            ->andWhere('ja.user = :user')
            ->setParameter('user', $user)
            ->orderBy('ja.appliedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findJobApplications(Job $job): array
    {
        return $this->createQueryBuilder('ja')
            ->leftJoin('ja.user', 'u')
            ->addSelect('u')
            ->andWhere('ja.job = :job')
            ->setParameter('job', $job)
            ->orderBy('ja.appliedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}