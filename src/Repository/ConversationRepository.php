<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function findByParticipant(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->where('p.id = :userId')
            ->andWhere('c.isArchived = false')
            ->setParameter('userId', $user->getId())
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findArchivedByParticipant(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->where('p.id = :userId')
            ->andWhere('c.isArchived = true')
            ->setParameter('userId', $user->getId())
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConversationBetweenUsers(User $user1, User $user2): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p1')
            ->innerJoin('c.participants', 'p2')
            ->where('p1.id = :user1Id')
            ->andWhere('p2.id = :user2Id')
            ->andWhere('SIZE(c.participants) = 2')
            ->setParameter('user1Id', $user1->getId())
            ->setParameter('user2Id', $user2->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Compte le nombre de messages non lus pour un utilisateur
     */
    public function getUnreadCount(User $user): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(m.id)')
            ->join('c.messages', 'm')
            ->join('c.participants', 'p')
            ->where('p = :user')
            ->andWhere('m.isRead = :isRead')
            ->andWhere('m.sender != :user')
            ->setParameter('user', $user)
            ->setParameter('isRead', false);
        
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouve une conversation existante entre un utilisateur et un ensemble de participants
     */
    public function findExistingConversation(User $user, array $participants): ?Conversation
    {
        // Si nous n'avons qu'un seul autre participant, cherchons une conversation privée
        if (count($participants) === 1) {
            return $this->findConversationBetweenUsers($user, $participants[0]);
        }
        
        // Pour plus de participants, la logique est plus complexe et peut nécessiter une approche différente
        // Cette implémentation simple ne gère que les conversations à deux participants
        return null;
    }

    /**
     * Recherche des conversations par mot-clé
     */
    public function searchConversations(User $user, string $query): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.participants', 'p')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('m.sender', 's')
            ->where('p.id = :userId')
            ->andWhere('c.isArchived = false')
            ->andWhere('(
                c.title LIKE :query OR 
                m.content LIKE :query OR 
                s.username LIKE :query OR
                s.firstName LIKE :query OR
                s.lastName LIKE :query OR
                EXISTS (
                    SELECT p2 FROM App\Entity\User p2 
                    WHERE p2 MEMBER OF c.participants AND 
                    (p2.username LIKE :query OR p2.firstName LIKE :query OR p2.lastName LIKE :query)
                )
            )')
            ->setParameter('userId', $user->getId())
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des conversations par mot-clé avec focus sur les messages des chercheurs d'emploi
     */
    public function searchConversationsByJobSeekerMessages(User $user, string $query): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'm', 's')  // Sélectionner explicitement les entités associées
            ->innerJoin('c.participants', 'p')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('m.sender', 's')
            ->where('p.id = :userId')
            ->andWhere('c.isArchived = false')
            ->andWhere('(
                c.title LIKE :query OR 
                (m.content LIKE :query AND :jobSeekerRole MEMBER OF s.roles) OR
                (s.username LIKE :query AND :jobSeekerRole MEMBER OF s.roles) OR
                (s.firstName LIKE :query AND :jobSeekerRole MEMBER OF s.roles) OR
                (s.lastName LIKE :query AND :jobSeekerRole MEMBER OF s.roles) OR
                EXISTS (
                    SELECT p2 FROM App\Entity\User p2 
                    WHERE p2 MEMBER OF c.participants AND 
                    (p2.username LIKE :query OR p2.firstName LIKE :query OR p2.lastName LIKE :query) AND
                    :jobSeekerRole MEMBER OF p2.roles
                )
            )')
            ->setParameter('userId', $user->getId())
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('jobSeekerRole', 'ROLE_JOB_SEEKER')
            ->orderBy('c.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des conversations et leurs messages par mot-clé
     */
    public function searchConversationsWithMessages(User $user, string $query): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c', 'm', 's')
            ->innerJoin('c.participants', 'p')
            ->leftJoin('c.messages', 'm')
            ->leftJoin('m.sender', 's')
            ->where('p.id = :userId')
            ->andWhere('c.isArchived = false')
            ->andWhere('(
                c.title LIKE :query OR 
                m.content LIKE :query OR 
                s.username LIKE :query OR
                s.firstName LIKE :query OR
                s.lastName LIKE :query
            )')
            ->setParameter('userId', $user->getId())
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('c.updatedAt', 'DESC')
            ->addOrderBy('m.createdAt', 'ASC');
        
        return $qb->getQuery()->getResult();
    }
}










