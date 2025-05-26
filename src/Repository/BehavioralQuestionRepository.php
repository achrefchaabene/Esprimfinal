<?php

namespace App\Repository;

use App\Entity\BehavioralQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BehavioralQuestion>
 */
class BehavioralQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BehavioralQuestion::class);
    }

    public function save(BehavioralQuestion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BehavioralQuestion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.subCategory = :category')
            ->setParameter('category', $category)
            ->orderBy('q.difficulty', 'ASC')
            ->getQuery()
            ->getResult();
    }
}