<?php

namespace App\Controller;

use App\Entity\BusinessCardHtml;
use App\Entity\User;
use App\Service\GeminiApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\EntrepriseProfileType;

class EntrepriseController extends AbstractController
{
    #[Route('/entreprise/edit-profile', name: 'entreprise_edit_profile')]
    public function editProfile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        $form = $this->createForm(EntrepriseProfileType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $logoFile = $form->get('logoFile')->getData();
            
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$logoFile->guessExtension();
                
                // Vérifier que le répertoire existe, sinon le créer
                $uploadDir = $this->getParameter('company_logos_directory');
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                try {
                    // Déplacer le fichier
                    $logoFile->move($uploadDir, $newFilename);
                    
                    // Supprimer l'ancien logo si nécessaire
                    $oldProfileImage = $user->getProfileImage();
                    if ($oldProfileImage) {
                        $oldImagePath = $this->getParameter('kernel.project_dir').'/public/'.$oldProfileImage;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    // Enregistrer le chemin relatif dans la base de données
                    // Important: le chemin doit être relatif à partir du répertoire public
                    $user->setProfileImage('uploads/company_logos/'.$newFilename);
                    
                    // Enregistrer les modifications
                    $entityManager->persist($user);
                    $entityManager->flush();
                    
                    $this->addFlash('success', 'Logo téléchargé avec succès !');
                    
                } catch (FileException $e) {
                    // Log l'erreur pour le débogage
                    error_log('Erreur lors du téléchargement du logo: ' . $e->getMessage());
                    
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement du logo: ' . $e->getMessage());
                }
            } else {
                // Si pas de logo, enregistrer quand même les autres modifications
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès.');
            }
            
            return $this->redirectToRoute('entreprise_edit_profile');
        }
        
        return $this->render('entreprise/edit_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    // Vérifier que la route est correctement définie
    #[Route('/entreprise', name: 'app_entreprise_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('entreprise/home.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/entreprise/carte-visite', name: 'entreprise_carte_visite')]
    public function carteVisite(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('entreprise/carte_visite.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/entreprise/profile-image-debug', name: 'entreprise_profile_image_debug')]
    public function profileImageDebug(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non connecté'], 403);
        }
        
        $profileImage = $user->getProfileImage();
        $imagePath = null;
        $imageExists = false;
        
        if ($profileImage) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/'.$profileImage;
            $imageExists = file_exists($imagePath);
        }
        
        return $this->json([
            'user_id' => $user->getId(),
            'username' => $user->getUsername(),
            'profile_image_path_in_db' => $profileImage,
            'full_image_path' => $imagePath,
            'image_exists' => $imageExists,
            'public_url' => $profileImage ? $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL).$profileImage : null
        ]);
    }

    #[Route('/entreprise/carte-visite/generate-ai', name: 'entreprise_generate_ai_card', methods: ['POST'])]
    public function generateAICard(Request $request, GeminiApiService $geminiAPI): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['description']) || !isset($data['companyName'])) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }
            
            // Construire le prompt pour l'IA
            $prompt = "Tu es un expert en design de cartes de visite professionnelles. Crée une carte de visite moderne avec les informations suivantes:\n\n";
            $prompt .= "Nom de l'entreprise: " . $data['companyName'] . "\n";
            $prompt .= "Secteur d'activité: " . ($data['industry'] ?? 'Non spécifié') . "\n";
            $prompt .= "Email: " . ($data['email'] ?? 'Non spécifié') . "\n";
            $prompt .= "Adresse: " . ($data['address'] ?? 'Non spécifié') . "\n\n";
            $prompt .= "Style demandé: " . $data['description'] . "\n\n";
            $prompt .= "Instructions spécifiques:\n";
            $prompt .= "1. Crée uniquement le code HTML et CSS pour une carte de visite moderne et professionnelle\n";
            $prompt .= "2. Utilise des dégradés de couleurs modernes et élégants\n";
            $prompt .= "3. Inclus des effets visuels subtils comme des ombres, des formes géométriques ou des motifs\n";
            $prompt .= "4. Assure-toi que le design est responsive et s'adapte à différentes tailles d'écran\n";
            $prompt .= "5. Utilise des polices modernes et lisibles\n";
            $prompt .= "6. Organise les informations de manière claire et professionnelle\n\n";
            $prompt .= "Réponds uniquement avec le code HTML et CSS de la carte de visite, sans explications. Le code doit être complet et prêt à être utilisé. N'inclus pas de balises <html>, <head> ou <body>, seulement le HTML et CSS nécessaire pour la carte elle-même.";
            
            // Appeler l'API Gemini
            $response = $geminiAPI->getChatResponse($prompt);
            
            // Extraire le code HTML de la réponse
            $htmlCode = $this->extractHtmlFromResponse($response);
            
            return new JsonResponse([
                'cardHtml' => $htmlCode
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/entreprise/carte-visite/save-ai', name: 'entreprise_save_ai_card', methods: ['POST'])]
    public function saveAICard(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['cardHtml'])) {
                return new JsonResponse(['error' => 'Données manquantes'], 400);
            }
            
            // Récupérer l'utilisateur connecté
            $user = $this->getUser();
            
            // Vérifier si l'utilisateur a déjà une carte de visite HTML
            $businessCardHtml = $user->getBusinessCardHtml();
            
            if (!$businessCardHtml) {
                // Créer une nouvelle entité BusinessCardHtml
                $businessCardHtml = new BusinessCardHtml();
                $businessCardHtml->setUser($user);
            }
            
            // Mettre à jour le contenu HTML
            $businessCardHtml->setHtmlContent($data['cardHtml']);
            
            // Persister les changements
            $entityManager->persist($businessCardHtml);
            $entityManager->flush();
            
            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Extraire le code HTML de la réponse de l'IA
     */
    private function extractHtmlFromResponse(string $response): string
    {
        // Nettoyer la réponse pour extraire uniquement le code HTML
        // Supprimer les balises de code markdown si présentes
        $response = preg_replace('/```html\s*/', '', $response);
        $response = preg_replace('/```\s*/', '', $response);
        
        // Supprimer les explications textuelles avant ou après le code HTML
        if (strpos($response, '<div') !== false) {
            $startPos = strpos($response, '<div');
            $endPos = strrpos($response, '</div>') + 6;
            if ($startPos !== false && $endPos !== false) {
                $response = substr($response, $startPos, $endPos - $startPos);
            }
        }
        
        return trim($response);
    }
}