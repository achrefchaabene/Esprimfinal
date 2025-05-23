<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :val')
            ->setParameter('val', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($user): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :published')
            ->setParameter('published', true);
        
        if (!empty($criteria['keywords'])) {
            $qb->andWhere('p.title LIKE :keywords OR p.description LIKE :keywords')
               ->setParameter('keywords', '%' . $criteria['keywords'] . '%');
        }
        
        if (!empty($criteria['category']) && $criteria['category'] !== 'all') {
            $qb->andWhere('p.category = :category')
               ->setParameter('category', $criteria['category']);
        }
        
        if (!empty($criteria['industry'])) {
            $qb->andWhere('p.industry = :industry')
               ->setParameter('industry', $criteria['industry']);
        }
        
        if (!empty($criteria['address'])) {
            $qb->andWhere('p.location LIKE :address')
               ->setParameter('address', '%' . $criteria['address'] . '%');
        }
        
        if (!empty($criteria['dateFrom'])) {
            $qb->andWhere('p.createdAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTime($criteria['dateFrom']));
        }
        
        if (!empty($criteria['dateTo'])) {
            $qb->andWhere('p.createdAt <= :dateTo')
               ->setParameter('dateTo', new \DateTime($criteria['dateTo']));
        }
        
        return $qb->orderBy('p.createdAt', 'DESC')
                 ->getQuery()
                 ->getResult();
    }
}
