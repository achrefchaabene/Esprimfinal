<?php

namespace App\Controller\Entreprise;

use App\Entity\Application;
use App\Entity\Publication;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entreprise/applications', name: 'entreprise_applications_')]
class ApplicationsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Récupérer toutes les publications de l'entreprise
        $publications = $em->getRepository(Publication::class)
            ->findBy(['user' => $this->getUser()]);
        
        // Récupérer toutes les candidatures pour ces publications
        $applications = $em->getRepository(Application::class)
            ->createQueryBuilder('a')
            ->join('a.publication', 'p')
            ->where('p.user = :company')
            ->setParameter('company', $this->getUser())
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('entreprise/applications/index.html.twig', [
            'applications' => $applications
        ]);
    }
    
    #[Route('/{id}/view', name: 'view')]
    public function view(Application $application): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que l'application appartient à une publication de l'entreprise
        if ($application->getPublication()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette candidature');
        }
        
        return $this->render('entreprise/applications/view.html.twig', [
            'application' => $application
        ]);
    }
    
    #[Route('/{id}/accept', name: 'accept')]
    public function accept(Application $application, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que l'application appartient à une publication de l'entreprise
        if ($application->getPublication()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette candidature');
        }
        
        $application->setStatus('accepted');
        $application->setProcessedAt(new \DateTimeImmutable());
        $em->flush();
        
        $this->addFlash('success', 'Candidature acceptée avec succès');
        return $this->redirectToRoute('entreprise_applications_view', ['id' => $application->getId()]);
    }
    
    #[Route('/{id}/reject', name: 'reject')]
    public function reject(Application $application, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que l'application appartient à une publication de l'entreprise
        if ($application->getPublication()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette candidature');
        }
        
        $application->setStatus('rejected');
        $application->setProcessedAt(new \DateTimeImmutable());
        
        // Récupérer le motif de rejet (optionnel)
        $rejectionReason = $request->request->get('rejection_reason');
        if ($rejectionReason) {
            $application->setFeedback($rejectionReason);
        }
        
        $em->flush();
        
        $this->addFlash('success', 'Candidature rejetée');
        return $this->redirectToRoute('entreprise_applications_view', ['id' => $application->getId()]);
    }
}