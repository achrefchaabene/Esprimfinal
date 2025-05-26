<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminDemandesController extends AbstractController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/admin/demandes', name: 'admin_demandes')]
    public function index(): Response
    {
        // Utilisez ROLE_COMPANY car c'est le rôle défini dans votre User entity
        $entreprises = $this->userService->findUsersByRole('ROLE_COMPANY');

        // Filtrer les entreprises non approuvées
        $entreprisesEnAttente = array_filter($entreprises, function($user) {
            return !$user->isApproved();
        });

        return $this->render('admin/demandes/index.html.twig', [
            'entreprises' => $entreprisesEnAttente
        ]);
    }

    #[Route('/admin/demandes/approuver/{email}', name: 'admin_demandes_approuver', methods: ['POST'])]
    public function approuver(Request $request, string $email): Response
    {
        if (!$this->isCsrfTokenValid('approve_company', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_demandes');
        }

        $user = $this->userService->findOneByEmail($email);

        if (!$user) {
            $this->addFlash('error', 'Entreprise non trouvée.');
            return $this->redirectToRoute('admin_demandes');
        }

        try {
            $this->userService->validateUser($user);
            $this->addFlash('success', 'Entreprise approuvée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'approbation.');
        }

        return $this->redirectToRoute('admin_demandes');
    }

    #[Route('/admin/demandes/rejeter/{email}', name: 'admin_demandes_rejeter', methods: ['POST'])]
    public function rejeter(Request $request, string $email): Response
    {
        if (!$this->isCsrfTokenValid('reject_company', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_demandes');
        }

        $user = $this->userService->findOneByEmail($email);

        if (!$user) {
            $this->addFlash('error', 'Entreprise non trouvée.');
            return $this->redirectToRoute('admin_demandes');
        }

        try {
            $this->userService->deleteUser($user);
            $this->addFlash('success', 'Entreprise rejetée avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors du rejet.');
        }

        return $this->redirectToRoute('admin_demandes');
    }
}