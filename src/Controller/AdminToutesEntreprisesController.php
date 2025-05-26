<?php
// src/Controller/AdminToutesEntreprisesController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;

#[IsGranted('ROLE_ADMIN')] // 🔐 Accès réservé aux admins
#[Route('/admin')]
class AdminToutesEntreprisesController extends AbstractController
{
    // Commentez ou supprimez cette route si elle est en conflit avec AdminController::companies
    // #[Route('/entreprises', name: 'admin_toutes_entreprises', methods: ['GET'])]
    // public function index(UserRepository $userRepository): Response
    // {
    //     // Récupère toutes les entreprises (type = 'company')
    //     $entreprises = $userRepository->findBy(['type' => 'company']);
    //     
    //     return $this->render('admin/ToutesEntreprises/index.html.twig', [
    //         'entreprises' => $entreprises
    //     ]);
    // }
}
