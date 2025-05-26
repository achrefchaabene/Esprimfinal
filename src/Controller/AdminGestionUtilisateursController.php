<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')] // 🔐 Accès réservé aux utilisateurs avec le rôle admin
class AdminGestionUtilisateursController extends AbstractController
{
    #[Route('/admin/gestion-utilisateurs', name: 'admin_gestion_utilisateurs')]
    public function index(): Response
    {
        return $this->render('admin/gestion_utilisateurs/index.html.twig');
    }

    #[Route('/admin/entreprises', name: 'admin_entreprises')]
    public function entreprises(): Response
    {
        // Exemple de données à remplacer par des données réelles (BDD ou service)
        $entreprises = [];

        return $this->render('admin/gestion_utilisateurs/entreprises.html.twig', [
            'entreprises' => $entreprises
        ]);
    }

    #[Route('/admin/chercheurs-emploi', name: 'admin_chercheurs_emploi')]
    public function chercheursEmploi(): Response
    {
        // Exemple de données à remplacer par des données réelles
        $chercheurs = [];

        return $this->render('admin/gestion_utilisateurs/chercheurs.html.twig', [
            'chercheurs' => $chercheurs
        ]);
    }

    #[Route('/admin/home', name: 'admin_home')]
    public function home(): Response
    {
        return $this->render('admin/home/index.html.twig'); // Page d'accueil admin
    }
}