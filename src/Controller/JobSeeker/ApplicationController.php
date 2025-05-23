<?php

namespace App\Controller\JobSeeker;

use App\Entity\User;
use App\Repository\JobApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/applications', name: 'job_seeker_applications')]
class ApplicationController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(JobApplicationRepository $applicationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer toutes les candidatures de l'utilisateur
        $applications = $applicationRepository->findUserApplications($user);
        
        return $this->render('job_seeker/applications.html.twig', [
            'applications' => $applications
        ]);
    }
    
    #[Route('/{id}', name: '_show', requirements: ['id' => '\d+'])]
    public function show(int $id, JobApplicationRepository $applicationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer la candidature spécifique
        $application = $applicationRepository->find($id);
        
        // Vérifier que la candidature existe et appartient à l'utilisateur
        if (!$application || $application->getUser() !== $user) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }
        
        return $this->render('job_seeker/application_details.html.twig', [
            'application' => $application
        ]);
    }
}