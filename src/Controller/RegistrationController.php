<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminRegistrationFormType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * Page de choix du type de compte
     */
    #[Route('/register/choice', name: 'app_register_choice')]
    public function choice(): Response
    {
        return $this->render('registration/choice.html.twig');
    }

    /**
     * Inscription administrateur
     */
    #[Route('/register/admin', name: 'app_register_admin')]
    public function registerAdmin(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(AdminRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement du formulaire...
        }

        return $this->render('registration/register_admin.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Inscription demandeur d'emploi
     */
    #[Route('/register/job-seeker', name: 'app_register_job_seeker')]
    public function registerJobSeeker(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Code pour l'inscription des demandeurs d'emploi
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            
            // Définir le type et les rôles
            $user->setType('job_seeker');
            $user->setRoles(['ROLE_USER']);
            
            // Persister l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre compte a été créé avec succès.');
            
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register_job_seeker.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Inscription entreprise
     */
    #[Route('/register/company', name: 'app_register_company')]
    public function registerCompany(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            
            // Définir le type et les rôles
            $user->setType('company');
            $user->setRoles(['ROLE_COMPANY']);
            
            // Récupérer les valeurs des champs personnalisés
            $companyName = $request->request->get('company_name');
            if ($companyName && method_exists($user, 'setCompanyName')) {
                $user->setCompanyName($companyName);
            }
            
            // Gérer le cas spécial de l'industrie
            $industry = $request->request->get('industry');
            $otherIndustry = $request->request->get('otherIndustry');
            
            if ($industry === 'Autre' && $otherIndustry) {
                // Si l'utilisateur a sélectionné "Autre" et a fourni une valeur personnalisée
                $industry = $otherIndustry;
            }
            
            if ($industry && method_exists($user, 'setIndustry')) {
                $user->setIndustry($industry);
            }
            
            // Persister l'entité
            $entityManager->persist($user);
            $entityManager->flush();
            
            $this->addFlash('success', 'Votre compte entreprise a été créé avec succès.');
            
            // Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register_company.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
