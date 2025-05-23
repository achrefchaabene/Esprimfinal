<?php

namespace App\Controller\Entreprise;

use App\Entity\Publication;
use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entreprise/publication', name: 'entreprise_publication_')]
class PublicationController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Récupérer les publications de l'entreprise connectée
        $publications = $publicationRepository->findByUser($this->getUser());
        
        return $this->render('entreprise/publications.html.twig', [
            'user' => $this->getUser(),
            'publications' => $publications,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        $publication = new Publication();
        $publication->setUser($this->getUser());
        $publication->setCreatedAt(new \DateTimeImmutable());
        
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publication);
            $entityManager->flush();
            
            $this->addFlash('success', 'Publication créée avec succès.');
            
            return $this->redirectToRoute('entreprise_publication_index');
        }
        
        return $this->render('entreprise/publication_form.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Publication $publication): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que la publication appartient à l'entreprise connectée
        if ($publication->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à voir cette publication.');
        }
        
        return $this->render('entreprise/publication_detail.html.twig', [
            'user' => $this->getUser(),
            'publication' => $publication,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que la publication appartient à l'entreprise connectée
        if ($publication->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette publication.');
        }
        
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $publication->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            
            $this->addFlash('success', 'Publication mise à jour avec succès.');
            
            return $this->redirectToRoute('entreprise_publication_show', ['id' => $publication->getId()]);
        }
        
        return $this->render('entreprise/publication_form.html.twig', [
            'user' => $this->getUser(),
            'publication' => $publication,
            'form' => $form->createView(),
            'edit_mode' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET'])]
    public function delete(Request $request, Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que la publication appartient à l'entreprise connectée
        if ($publication->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette publication.');
        }
        
        $entityManager->remove($publication);
        $entityManager->flush();
        
        $this->addFlash('success', 'Publication supprimée avec succès.');
        
        return $this->redirectToRoute('entreprise_publication_index');
    }

    #[Route('/{id}/publish', name: 'publish', methods: ['GET'])]
    public function publish(Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que la publication appartient à l'entreprise connectée
        if ($publication->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à publier cette publication.');
        }
        
        $publication->setIsPublished(true);
        $publication->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();
        
        $this->addFlash('success', 'Publication publiée avec succès.');
        
        return $this->redirectToRoute('entreprise_publication_show', ['id' => $publication->getId()]);
    }

    #[Route('/{id}/unpublish', name: 'unpublish', methods: ['GET'])]
    public function unpublish(Publication $publication, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_COMPANY');
        
        // Vérifier que la publication appartient à l'entreprise connectée
        if ($publication->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à dépublier cette publication.');
        }
        
        $publication->setIsPublished(false);
        $publication->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();
        
        $this->addFlash('success', 'Publication dépubliée avec succès.');
        
        return $this->redirectToRoute('entreprise_publication_show', ['id' => $publication->getId()]);
    }
}


