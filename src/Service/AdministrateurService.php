<?php
// src/Service/AdministrateurService.php
namespace App\Service;

use App\Entity\Administrateur;
use App\Entity\User;
use App\Repository\AdministrateurRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;

class AdministrateurService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private AdministrateurRepository $administrateurRepository
    ) {
    }

    public function creerAdministrateur(
        string $username,
        string $email,
        string $plainPassword,
        string $prenom,
        string $nomComplet,
        ?string $photo = null
    ): ?Administrateur {
        // Créer l'utilisateur de base
        $user = $this->userService->createUser(
            $username,
            $email,
            $plainPassword,
            'administrateur',
            $photo,
            true, // est_valide
            true  // est_actif
        );

        if (!$user) {
            return null;
        }

        // Créer l'administrateur
        $administrateur = new Administrateur();
        $administrateur->setPrenom($prenom);
        $administrateur->setNomComplet($nomComplet);
        $administrateur->setUser($user);

        $this->entityManager->persist($administrateur);
        $this->entityManager->flush();

        return $administrateur;
    }

    public function getAdministrateurById(int $id): ?Administrateur
    {
        return $this->administrateurRepository->find($id);
    }

    public function getAdministrateurByUserId(int $userId): ?Administrateur
    {
        return $this->administrateurRepository->findOneByUserId($userId);
    }

    public function updateAdministrateur(Administrateur $administrateur): bool
    {
        try {
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}