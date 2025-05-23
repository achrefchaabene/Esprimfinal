<?php

namespace App\Controller\JobSeeker;

use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/job-seeker', name: 'job_seeker_')]
class JobController extends AbstractController
{
    #[Route('/jobs', name: 'jobs_index')]
    public function index(PublicationRepository $publicationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $publications = $publicationRepository->findPublished();
        
        return $this->render('job_seeker/job_search.html.twig', [
            'publications' => $publications,
        ]);
    }

    #[Route('/job/{id}', name: 'job_details')]
    public function details(Request $request, PublicationRepository $publicationRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        // Récupérer la publication manuellement
        $publication = $publicationRepository->find($id);
        
        // Vérifier si la publication existe
        if (!$publication) {
            $this->addFlash('error', 'L\'offre d\'emploi demandée n\'existe pas.');
            return $this->redirectToRoute('job_seeker_jobs_index');
        }
        
        // Vérifier que la publication est publiée
        if (!$publication->isIsPublished()) {
            $this->addFlash('error', 'Cette offre d\'emploi n\'est pas disponible.');
            return $this->redirectToRoute('job_seeker_jobs_index');
        }
        
        return $this->render('job_seeker/job_details.html.twig', [
            'publication' => $publication,
        ]);
    }
}







