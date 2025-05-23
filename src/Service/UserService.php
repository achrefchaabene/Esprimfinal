<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Trouve tous les utilisateurs d'un certain rôle (ex: 'ROLE_COMPANY')
     */
    public function findUsersByRole(string $role): array
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where(':role MEMBER OF u.roles')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult();
    }

    /**
     * Cherche un utilisateur par email
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    /**
     * Valide/approuve un utilisateur
     */
    public function validateUser(User $user): void
    {
        if ($user->getType() === 'company') {
            $user->setIsApproved(true);
            $this->entityManager->flush();
        }
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}