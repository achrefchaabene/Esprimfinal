<?php
// src/Service/UserService.php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
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
        return $this->userRepository->findOneByEmail($email);
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

    /**
     * Crée un nouvel utilisateur
     *
     * @param string $username Nom d'utilisateur
     * @param string $email Email
     * @param string $plainPassword Mot de passe en clair
     * @param string $type Type d'utilisateur (job_seeker, company, administrateur)
     * @param string|null $profileImage Chemin de l'image de profil
     * @param bool $isApproved Si l'utilisateur est approuvé
     * @param bool $isActive Si l'utilisateur est actif
     * @return User|null L'utilisateur créé ou null en cas d'erreur
     */
    public function createUser(
        string $username,
        string $email,
        string $plainPassword,
        string $type,
        ?string $profileImage = null,
        bool $isApproved = false,
        bool $isActive = true
    ): ?User {
        try {
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            
            // Définir le type et les rôles
            $user->setType($type);
            
            // Définir les rôles en fonction du type
            switch ($type) {
                case 'administrateur':
                    $user->setRoles(['ROLE_ADMIN']);
                    break;
                case 'company':
                    $user->setRoles(['ROLE_COMPANY']);
                    break;
                case 'job_seeker':
                    $user->setRoles(['ROLE_USER']);
                    break;
                default:
                    $user->setRoles(['ROLE_USER']);
            }
            
            // Définir l'image de profil si fournie
            if ($profileImage) {
                $user->setProfileImage($profileImage);
            }
            
            // Définir si l'utilisateur est approuvé et actif
            if (method_exists($user, 'setIsApproved')) {
                $user->setIsApproved($isApproved);
            }
            
            if (method_exists($user, 'setIsActive')) {
                $user->setIsActive($isActive);
            }
            
            // Persister l'utilisateur
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            
            return $user;
        } catch (\Exception $e) {
            // Gérer l'erreur (log, etc.)
            return null;
        }
    }
}
