<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // 🔐 Protège l'accès à toutes les routes du contrôleur
#[Route('/admin')]
class AdminHomeController extends AbstractController
{
    #[Route('/', name: 'admin_home', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/home/index.html.twig');
    }
}