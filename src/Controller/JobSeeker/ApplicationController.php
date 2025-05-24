<?php

namespace App\Controller\JobSeeker;

use App\Entity\User;
use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/applications', name: 'job_seeker_applications')]
class ApplicationController extends AbstractController
{
    #[Route('/', name: '')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer toutes les candidatures de l'utilisateur
        $applications = $entityManager->getRepository(Application::class)
            ->findBy(['user' => $user], ['createdAt' => 'DESC']);
        
        return $this->render('job_seeker/applications.html.twig', [
            'applications' => $applications
        ]);
    }
    
    #[Route('/{id}/withdraw', name: '_withdraw')]
    public function withdraw(int $id, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Récupérer la candidature
        $application = $entityManager->getRepository(Application::class)->find($id);
        
        // Vérifier que la candidature existe et appartient à l'utilisateur
        if (!$application || $application->getUser() !== $user) {
            throw $this->createNotFoundException('Candidature non trouvée');
        }
        
        // Vérifier que la candidature est en attente
        if ($application->getStatus() !== 'pending') {
            $this->addFlash('error', 'Vous ne pouvez pas retirer une candidature qui a déjà été traitée');
            return $this->redirectToRoute('job_seeker_applications');
        }
        
        // Supprimer la candidature
        $entityManager->remove($application);
        $entityManager->flush();
        
        $this->addFlash('success', 'Votre candidature a été retirée avec succès');
        return $this->redirectToRoute('job_seeker_applications');
    }
}
