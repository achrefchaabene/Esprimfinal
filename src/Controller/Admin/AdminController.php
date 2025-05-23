<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Statistiques pour le tableau de bord
        $stats = [
            'totalUsers' => $em->getRepository(User::class)->count([]),
            'totalCompanies' => $em->getRepository(User::class)->count(['roles' => 'ROLE_COMPANY']),
            'totalJobSeekers' => $em->getRepository(User::class)->count(['roles' => 'ROLE_JOB_SEEKER']),
            'pendingCompanies' => $em->getRepository(User::class)->count(['roles' => 'ROLE_COMPANY', 'isApproved' => false]),
        ];
        
        // Récupérer les dernières entreprises inscrites
        $latestCompanies = $em->getRepository(User::class)->findBy(
            ['roles' => 'ROLE_COMPANY'],
            ['createdAt' => 'DESC'],
            5
        );
        
        return $this->render('admin/index.html.twig', [
            'stats' => $stats,
            'latestCompanies' => $latestCompanies
        ]);
    }
    
    #[Route('/companies', name: 'companies')]
    public function companies(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer toutes les entreprises
        $companies = $userRepository->findByRole('ROLE_COMPANY');
        
        return $this->render('admin/companies.html.twig', [
            'companies' => $companies
        ]);
    }
    
    #[Route('/pending-companies', name: 'pending_companies')]
    public function pendingCompanies(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Récupérer les entreprises en attente d'approbation
        $pendingCompanies = $userRepository->findBy([
            'roles' => 'ROLE_COMPANY',
            'isApproved' => false
        ]);
        
        return $this->render('admin/pending_companies.html.twig', [
            'companies' => $pendingCompanies
        ]);
    }
    
    #[Route('/company/{id}/approve', name: 'approve_company')]
    public function approveCompany(User $company, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifier que l'utilisateur est bien une entreprise
        if (!in_array('ROLE_COMPANY', $company->getRoles())) {
            $this->addFlash('error', 'Cet utilisateur n\'est pas une entreprise.');
            return $this->redirectToRoute('admin_companies');
        }
        
        // Approuver l'entreprise
        $company->setIsApproved(true);
        $em->flush();
        
        $this->addFlash('success', 'L\'entreprise a été approuvée avec succès.');
        return $this->redirectToRoute('admin_companies');
    }
    
    #[Route('/company/{id}/delete', name: 'delete_company')]
    public function deleteCompany(User $company, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Supprimer l'entreprise
        $em->remove($company);
        $em->flush();
        
        $this->addFlash('success', 'L\'entreprise a été supprimée avec succès.');
        return $this->redirectToRoute('admin_companies');
    }
}