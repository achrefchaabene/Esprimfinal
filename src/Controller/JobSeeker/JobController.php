<?php

namespace App\Controller\JobSeeker;

use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function details(PublicationRepository $publicationRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $publication = $publicationRepository->find($id);
        
        // Vérifiez que la publication existe
        if (!$publication) {
            throw $this->createNotFoundException('Publication non trouvée');
        }
        
        return $this->render('job_seeker/job_details.html.twig', [
            'publication' => $publication,
        ]);
    }
}






