<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Trouve les utilisateurs par type
     */
    public function findByType(string $type): array
    {
        return $this->findBy(['type' => $type]);
    }

    /**
     * Recherche des utilisateurs par nom ou email
     */
    public function searchUsers(string $term): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.username LIKE :term')
            ->orWhere('u.email LIKE :term')
            ->orWhere('u.firstName LIKE :term')
            ->orWhere('u.lastName LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs récemment inscrits
     */
    public function findRecentUsers(int $limit = 10): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs par rôle (méthode alternative)
     */
    public function findUsersByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where(':role MEMBER OF u.roles')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les utilisateurs ayant un rôle spécifique
     */
    public function findByRole(string $role)
    {
        $qb = $this->createQueryBuilder('u');
        
        return $qb
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"'.$role.'"%')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les utilisateurs sauf celui spécifié, avec filtrage optionnel par rôle
     */
    public function findAllExcept(User $user, ?string $role = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.id != :userId')
            ->setParameter('userId', $user->getId());
        
        if ($role) {
            $qb->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
               ->setParameter('role', json_encode($role));
        }
        
        return $qb->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les chercheurs d'emploi sauf l'utilisateur spécifié
     */
    public function findJobSeekers(User $currentUser): array
    {
        // Récupérer tous les utilisateurs sauf l'utilisateur courant
        $users = $this->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $currentUser->getId())
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Filtrer manuellement pour ne garder que les chercheurs d'emploi
        return array_filter($users, function(User $user) {
            return in_array('ROLE_JOB_SEEKER', $user->getRoles());
        });
    }
}











