<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        // Rediriger en fonction du rôle de l'utilisateur
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_first_page');
        }
        
        if (in_array('ROLE_JOB_SEEKER', $user->getRoles())) {
            return $this->redirectToRoute('job_seeker_home');
        }
        
        if (in_array('ROLE_COMPANY', $user->getRoles())) {
            return $this->redirectToRoute('app_entreprise_home');
        }
        
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->redirectToRoute('admin_dashboard');
        }
        
        return $this->redirectToRoute('app_first_page');
    }
}
