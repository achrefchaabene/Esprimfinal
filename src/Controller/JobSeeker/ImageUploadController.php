<?php

namespace App\Controller\JobSeeker;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/job-seeker/image', name: 'job_seeker_image_')]
class ImageUploadController extends AbstractController
{
    #[Route('/upload', name: 'upload', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger, Connection $connection): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        // Récupérer le fichier uploadé
        $imageFile = $request->files->get('profile_image');
        
        if (!$imageFile) {
            $this->addFlash('error', 'Aucun fichier n\'a été téléchargé.');
            return $this->redirectToRoute('job_seeker_profile_edit');
        }
        
        // Générer un nom de fichier unique
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
        
        // Définir le répertoire de destination
        $uploadDir = $this->getParameter('profile_images_directory');
        
        // Créer le répertoire s'il n'existe pas
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        try {
            // Déplacer le fichier
            $imageFile->move($uploadDir, $newFilename);
            
            // Chemin relatif pour la base de données
            $imagePath = 'uploads/profile_images/'.$newFilename;
            
            // Mettre à jour la base de données directement avec une requête SQL
            $userId = $user->getId();
            $stmt = $connection->prepare('UPDATE user SET profile_image = ? WHERE id = ?');
            $result = $stmt->executeStatement([$imagePath, $userId]);
            
            if ($result > 0) {
                $this->addFlash('success', 'Image de profil mise à jour avec succès.');
            } else {
                $this->addFlash('error', 'Erreur lors de la mise à jour de l\'image dans la base de données.');
            }
            
        } catch (FileException $e) {
            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image: ' . $e->getMessage());
        }
        
        return $this->redirectToRoute('job_seeker_profile_edit');
    }
}