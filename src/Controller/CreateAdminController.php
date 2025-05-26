<?php
// src/Controller/CreateAdminController.php

namespace App\Controller;

use App\Entity\Administrateur;
use App\Form\AdministrateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Modification ici

class CreateAdminController extends AbstractController
{
    #[Route("/admin/create", name: "create_admin")]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher // Modification ici
    ): Response
    {
        $administrateur = new Administrateur();
        $form = $this->createForm(AdministrateurType::class, $administrateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du mot de passe en clair
            $plainPassword = $administrateur->getMotdepasse();

            // Hachage avec la nouvelle interface
            $hashedPassword = $passwordHasher->hashPassword(
                $administrateur,
                $plainPassword
            );

            $administrateur->setMotdepasse($hashedPassword);

            $entityManager->persist($administrateur);
            $entityManager->flush();

            $this->addFlash('success', 'Administrateur créé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}