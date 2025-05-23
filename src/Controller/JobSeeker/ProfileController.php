<?php

namespace App\Controller\JobSeeker;

use App\Entity\User;
use App\Form\JobSeekerProfileCustomType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\DBAL\Connection;

#[Route('/job-seeker/profile', name: 'job_seeker_profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/profile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/edit', name: 'edit')]
    public function editProfile(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer les données actuelles de l'utilisateur
        $userId = $user->getId();
        $connection = $entityManager->getConnection();
        $stmt = $connection->prepare('SELECT * FROM user WHERE id = ?');
        $result = $stmt->executeQuery([$userId]);
        $userData = $result->fetchAssociative();
        
        // Préparer les données pour le formulaire
        $formData = [
            'firstName' => $userData['first_name'],
            'lastName' => $userData['last_name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'],
            'address' => $userData['address'],
            'title' => $userData['title'],
            'about' => $userData['about'],
            'experience' => $userData['experience'],
            'education' => $userData['education'],
            'skills' => $userData['skills'],
        ];
        
        $form = $this->createForm(JobSeekerProfileCustomType::class, null, [
            'user_data' => $formData
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $profileImageFile = $form->get('profileImageFile')->getData();
            
            // Si un nouveau fichier est uploadé, le traiter
            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$profileImageFile->guessExtension();
                
                $uploadDir = $this->getParameter('profile_images_directory');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                try {
                    // Déplacer le fichier
                    $profileImageFile->move($uploadDir, $newFilename);
                    
                    // Supprimer l'ancienne image si nécessaire
                    $oldProfileImage = $userData['profile_image'];
                    if ($oldProfileImage && $oldProfileImage !== 'img/fxchat.png') {
                        $oldImagePath = $this->getParameter('kernel.project_dir').'/public/'.$oldProfileImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    // Définir le nouveau chemin d'image
                    $profileImage = 'uploads/profile_images/'.$newFilename;
                    $this->addFlash('success', 'Photo téléchargée avec succès !');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de la photo: ' . $e->getMessage());
                    $profileImage = $userData['profile_image'];
                }
            } else {
                $profileImage = $userData['profile_image'];
            }
            
            // Mettre à jour directement dans la base de données
            $stmt = $connection->prepare('
                UPDATE user 
                SET first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone = ?, 
                    address = ?, 
                    title = ?, 
                    about = ?, 
                    experience = ?, 
                    education = ?, 
                    skills = ?,
                    profile_image = ?
                WHERE id = ?
            ');
            
            $stmt->executeStatement([
                $formData['firstName'],
                $formData['lastName'],
                $formData['email'],
                $formData['phone'],
                $formData['address'],
                $formData['title'],
                $formData['about'],
                $formData['experience'],
                $formData['education'],
                $formData['skills'],
                $profileImage,
                $userId
            ]);
            
            // Mettre à jour également l'objet User en mémoire
            $user->setProfileImage($profileImage);
            
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            
            // Rediriger vers la page de profil pour éviter de retraiter le formulaire
            return $this->redirectToRoute('job_seeker_profile_index');
        }
        
        return $this->render('job_seeker/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'currentProfileImage' => $userData['profile_image']
        ]);
    }
}
