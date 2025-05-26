<?php

namespace App\Controller;

use App\Entity\Administrateur;
use App\Form\CreateAdministrateurType;
use App\Service\AdministrateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[IsGranted('ROLE_ADMIN')]
class AdminCreationController extends AbstractController
{
    private $administrateurService;

    public function __construct(AdministrateurService $administrateurService)
    {
        $this->administrateurService = $administrateurService;
    }

    #[Route('/admin/create-admin', name: 'admin_create_admin')]
    public function createAdmin(Request $request, SluggerInterface $slugger): Response
    {
        $administrateur = new Administrateur();
        $form = $this->createForm(CreateAdministrateurType::class, $administrateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $form->get('username')->getData();
            $email = $form->get('email')->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            $prenom = $administrateur->getPrenom();
            $nomComplet = $administrateur->getNomComplet();
            
            // Gestion de la photo
            $photoFile = $form->get('photo')->getData();
            $photoFilename = null;
            
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $photoFilename = $newFilename;
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo');
                }
            }
            
            // Création de l'administrateur
            $admin = $this->administrateurService->creerAdministrateur(
                $username,
                $email,
                $plainPassword,
                $prenom,
                $nomComplet,
                $photoFilename
            );
            
            if ($admin) {
                $this->addFlash('success', 'Administrateur créé avec succès !');
                return $this->redirectToRoute('admin_dashboard');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'administrateur');
            }
        }
        
        return $this->render('admin/create_admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}