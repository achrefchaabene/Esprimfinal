<?php

namespace App\Repository;

use App\Entity\InterviewQuestion;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InterviewQuestion>
 */
class InterviewQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterviewQuestion::class);
    }

    /**
     * Trouve toutes les questions avec un indicateur si elles sont sauvegardées par l'utilisateur
     */
    public function findAllWithSavedStatus(User $user)
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT q.*, 
                   CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END as is_saved
            FROM interview_question q
            LEFT JOIN saved_interview_question s ON q.id = s.question_id AND s.user_id = :userId
            ORDER BY q.category, q.id
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['userId' => $user->getId()]);
        
        return $resultSet->fetchAllAssociative();
    }

    /**
     * Trouve toutes les questions sauvegardées par l'utilisateur
     */
    public function findSavedByUser(User $user)
    {
        return $this->createQueryBuilder('q')
            ->join('App\Entity\SavedInterviewQuestion', 's', 'WITH', 's.question = q.id')
            ->where('s.user = :user')
            ->setParameter('user', $user)
            ->orderBy('q.category', 'ASC')
            ->addOrderBy('q.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

