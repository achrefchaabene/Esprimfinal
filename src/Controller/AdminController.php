<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur a le rôle ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer les statistiques
        $userRepository = $entityManager->getRepository(User::class);
        
        $totalUsers = $userRepository->count([]);
        $candidateCount = $userRepository->count(['type' => 'job_seeker']);
        $companyCount = $userRepository->count(['type' => 'company']);
        
        // Récupérer les derniers candidats
        $lastCandidates = $userRepository->findBy(
            ['type' => 'job_seeker'],
            ['id' => 'DESC'],
            5
        );
        
        // Récupérer les entreprises en attente
        $pendingCompanies = $userRepository->findBy(
            ['type' => 'company', 'isApproved' => false],
            ['id' => 'DESC']
        );
        
        return $this->render('admin/index.html.twig', [
            'totalUsers' => $totalUsers,
            'candidateCount' => $candidateCount,
            'companyCount' => $companyCount,
            'lastCandidates' => $lastCandidates,
            'pendingCompanies' => $pendingCompanies,
        ]);
    }
    
    #[Route('/admin', name: 'admin_home')]
    public function index(): Response
    {
        // Rediriger vers le tableau de bord
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/companies', name: 'admin_companies')]
    public function companies(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $userRepository = $entityManager->getRepository(User::class);
        $companies = $userRepository->findBy(['type' => 'company']);
        
        return $this->render('admin/ToutesEntreprises/index.html.twig', [
            'entreprises' => $companies,
        ]);
    }

    #[Route('/admin/job-seekers', name: 'admin_job_seekers')]
    public function jobSeekers(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $userRepository = $entityManager->getRepository(User::class);
        $jobSeekers = $userRepository->findBy(['type' => 'job_seeker']);
        
        return $this->render('admin/GestionUtilisateurs/chercheurs.html.twig', [
            'chercheurs' => $jobSeekers,
        ]);
    }

    #[Route('/admin/create-admin', name: 'admin_create_admin')]
    public function createAdmin(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('admin/create_admin.html.twig');
    }

    #[Route('/admin/company/approve/{id}', name: 'admin_approve_company', methods: ['POST'])]
    public function approveCompany(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $userRepository = $entityManager->getRepository(User::class);
        $company = $userRepository->find($id);
        
        if (!$company) {
            $this->addFlash('error', 'Entreprise non trouvée.');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        if ($company->getType() !== 'company') {
            $this->addFlash('error', 'Cet utilisateur n\'est pas une entreprise.');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        // Approuver l'entreprise
        $company->setIsApproved(true);
        $entityManager->flush();
        
        $this->addFlash('success', 'L\'entreprise a été approuvée avec succès.');
        
        // Rediriger vers la page précédente ou le tableau de bord
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/company/reject/{id}', name: 'admin_reject_company', methods: ['POST'])]
    public function rejectCompany(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $userRepository = $entityManager->getRepository(User::class);
        $company = $userRepository->find($id);
        
        if (!$company) {
            $this->addFlash('error', 'Entreprise non trouvée.');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        if ($company->getType() !== 'company') {
            $this->addFlash('error', 'Cet utilisateur n\'est pas une entreprise.');
            return $this->redirectToRoute('admin_dashboard');
        }
        
        // Option 1: Rejeter l'entreprise en mettant isApproved à false
        $company->setIsApproved(false);
        $entityManager->flush();
        
        // Option 2: Supprimer l'entreprise (décommentez si vous préférez cette option)
        // $entityManager->remove($company);
        // $entityManager->flush();
        
        $this->addFlash('success', 'L\'entreprise a été rejetée avec succès.');
        
        // Rediriger vers la page précédente ou le tableau de bord
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('admin_dashboard');
    }
}
