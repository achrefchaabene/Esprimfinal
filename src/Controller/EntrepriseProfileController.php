<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EntrepriseProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/entreprise/edit-profile', name: 'entreprise_edit_profile')]
class EntrepriseProfileController extends AbstractController
{
    public function editProfile(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est connecté et est une entreprise
        if (!$user instanceof User || $user->getType() !== 'company') {
            return $this->redirectToRoute('app_login');
        }
        
        // Créer le formulaire
        $form = $this->createForm(EntrepriseProfileType::class, $user);
        $form->handleRequest($request);
        
        // Traiter le formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications
            $entityManager->flush();
            
            // Ajouter un message flash de succès
            $this->addFlash('success', 'Les modifications ont été enregistrées avec succès.');
            
            // Rediriger vers la page d'accueil de l'entreprise
            return $this->redirectToRoute('entreprise_home');
        }
        
        // Afficher le formulaire
        return $this->render('entreprise/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}

