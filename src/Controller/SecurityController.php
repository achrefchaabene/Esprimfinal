<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection si déjà connecté
        if ($this->getUser()) {
            return $this->redirectBasedOnRole();
        }

        // Gestion des erreurs
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // La déconnexion est gérée par le firewall
        throw new \LogicException('This method will be intercepted by the logout key on your firewall.');
    }

    /**
     * Redirige l'utilisateur en fonction de son rôle
     */
    private function redirectBasedOnRole(): Response
    {
        $user = $this->getUser();
        
        if ($user === null) {
            return $this->redirectToRoute('app_first_page');
        }

        if (in_array('ROLE_JOB_SEEKER', $user->getRoles())) {
            return $this->redirectToRoute('job_seeker_home');
        }

        if (in_array('ROLE_COMPANY', $user->getRoles())) {
            // Utiliser la route correcte app_entreprise_home
            return $this->redirectToRoute('app_entreprise_home');
        }

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->redirectToRoute('app_first_page');
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function forgotPassword(Request $request): Response
    {
        // Logique temporaire en attendant l'intégration complète
        $this->addFlash('warning', 'Password reset functionality is coming soon!');
        return $this->redirectToRoute('app_login');
    }
}
