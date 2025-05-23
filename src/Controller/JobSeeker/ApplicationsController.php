<?php

namespace App\Controller\JobSeeker;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/job-seeker/applications', name: 'job_seeker_applications_')]
class ApplicationsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer les candidatures de l'utilisateur
        $applications = $entityManager->getRepository(YourApplicationEntity::class)
            ->findBy(['user' => $user]);
        
        return $this->render('job_seeker/applications.html.twig', [
            'applications' => $applications,
        ]);
    }
}