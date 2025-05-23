<?php

namespace App\Controller\Entreprise;

use App\Entity\Publication;
use App\Repository\JobApplicationRepository;
use App\Repository\PublicationRepository;
use App\Repository\SavedJobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entreprise/statistics', name: 'entreprise_statistics_')]
class StatisticsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        PublicationRepository $publicationRepository,
        JobApplicationRepository $jobApplicationRepository,
        SavedJobRepository $savedJobRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        $user = $this->getUser();
        
        // Récupérer les publications de l'entreprise
        $publications = $publicationRepository->findByUser($user);
        
        // Statistiques globales
        $totalPublications = count($publications);
        $publishedPublications = count(array_filter($publications, function(Publication $pub) {
            return $pub->isIsPublished();
        }));
        
        // Récupérer les IDs des publications
        $publicationIds = array_map(function(Publication $pub) {
            return $pub->getId();
        }, $publications);
        
        // Statistiques d'applications
        $totalApplications = $jobApplicationRepository->countByPublications($publicationIds);
        
        // Statistiques de sauvegarde
        $totalSaved = $savedJobRepository->countByPublications($publicationIds);
        
        // Statistiques de vues
        $totalViews = 0;
        foreach ($publications as $publication) {
            $totalViews += $publication->getViewCount() ?? 0;
        }
        
        // Statistiques par publication
        $publicationStats = [];
        foreach ($publications as $publication) {
            $publicationStats[] = [
                'id' => $publication->getId(),
                'title' => $publication->getTitle(),
                'category' => $publication->getCategory(),
                'isPublished' => $publication->isIsPublished(),
                'createdAt' => $publication->getCreatedAt(),
                'views' => $publication->getViewCount() ?? 0,
                'applications' => $jobApplicationRepository->countByPublication($publication->getId()),
                'saved' => $savedJobRepository->countByPublication($publication->getId()),
            ];
        }
        
        return $this->render('entreprise/statistics.html.twig', [
            'user' => $user,
            'globalStats' => [
                'totalPublications' => $totalPublications,
                'publishedPublications' => $publishedPublications,
                'totalApplications' => $totalApplications,
                'totalSaved' => $totalSaved,
                'totalViews' => $totalViews,
            ],
            'publicationStats' => $publicationStats,
        ]);
    }
}