<?php

namespace App\Repository;

use App\Entity\Publication;
use App\Entity\User;
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

    /**
     * Trouve toutes les publications publiées
     */
    public function findPublished()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :val')
            ->setParameter('val', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Trouve les publications d'un utilisateur
     */
    public function findByUser(User $user)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Trouve les publications par catégorie
     */
    public function findByCategory(string $category)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->andWhere('p.isPublished = :published')
            ->setParameter('category', $category)
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Recherche des publications par terme de recherche
     */
    public function searchPublications(string $searchTerm, ?string $category = null)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :published')
            ->setParameter('published', true)
            ->andWhere('p.title LIKE :searchTerm OR p.content LIKE :searchTerm OR p.user.companyName LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$searchTerm.'%')
            ->orderBy('p.createdAt', 'DESC');
        
        if ($category && $category !== 'all') {
            $query->andWhere('p.category = :category')
                ->setParameter('category', $category);
        }
        
        return $query->getQuery()->getResult();
    }

    /**
     * Recherche avancée des publications
     */
    public function advancedSearch(array $criteria)
    {
        $query = $this->createQueryBuilder('p')
            ->join('p.user', 'u')
            ->andWhere('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC');
        
        // Recherche par mot-clé
        if (!empty($criteria['keywords'])) {
            $query->andWhere('p.title LIKE :keywords OR p.content LIKE :keywords')
                ->setParameter('keywords', '%'.$criteria['keywords'].'%');
        }
        
        // Recherche par secteur d'activité
        if (!empty($criteria['industry'])) {
            $query->andWhere('u.industry = :industry')
                ->setParameter('industry', $criteria['industry']);
        }
        
        // Recherche par catégorie
        if (!empty($criteria['category']) && $criteria['category'] !== 'all') {
            $query->andWhere('p.category = :category')
                ->setParameter('category', $criteria['category']);
        }
        
        // Recherche par date de publication (après)
        if (!empty($criteria['dateFrom'])) {
            $query->andWhere('p.createdAt >= :dateFrom')
                ->setParameter('dateFrom', $criteria['dateFrom']);
        }
        
        // Recherche par date de publication (avant)
        if (!empty($criteria['dateTo'])) {
            $query->andWhere('p.createdAt <= :dateTo')
                ->setParameter('dateTo', $criteria['dateTo']);
        }
        
        // Recherche par lieu
        if (!empty($criteria['location'])) {
            $query->andWhere('u.location LIKE :location')
                ->setParameter('location', '%'.$criteria['location'].'%');
        }
        
        return $query->getQuery()->getResult();
    }
}
