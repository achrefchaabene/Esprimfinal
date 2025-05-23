<?php

namespace App\Service;

use Twig\Environment;

class BusinessCardService
{
    private $twig;
    private $geminiApiService;
    private $templates = [
        'classic_blue' => 'business_card/classic_blue.html.twig',
        'modern_minimalist' => 'business_card/modern_minimalist.html.twig',
        'creative_dark' => 'business_card/creative_dark.html.twig',
        'ai_generated' => null // Ce template sera généré par l'IA
    ];

    public function __construct(Environment $twig, GeminiApiService $geminiApiService)
    {
        $this->twig = $twig;
        $this->geminiApiService = $geminiApiService;
    }

    public function generateCard(array $data, string $templateId): string
    {
        // Si c'est un template AI-generated
        if ($templateId === 'ai_generated') {
            return $this->generateAICard($data);
        }

        // Vérifier si le template existe
        if (!isset($this->templates[$templateId])) {
            throw new \InvalidArgumentException("Template ID '{$templateId}' not found");
        }

        // Rendre le template avec les données
        return $this->twig->render($this->templates[$templateId], [
            'data' => $data
        ]);
    }

    private function generateAICard(array $data): string
    {
        // Construire le prompt pour l'IA
        $prompt = "Génère une carte de visite HTML/CSS moderne et professionnelle avec les informations suivantes:\n\n";
        $prompt .= "Nom: " . ($data['name'] ?? 'Non spécifié') . "\n";
        $prompt .= "Titre: " . ($data['title'] ?? 'Non spécifié') . "\n";
        $prompt .= "Entreprise: " . ($data['company'] ?? 'Non spécifié') . "\n";
        $prompt .= "Téléphone: " . ($data['phone'] ?? 'Non spécifié') . "\n";
        $prompt .= "Email: " . ($data['email'] ?? 'Non spécifié') . "\n";
        $prompt .= "Site web: " . ($data['website'] ?? 'Non spécifié') . "\n";
        $prompt .= "Adresse: " . ($data['address'] ?? 'Non spécifié') . "\n\n";
        
        if (isset($data['social_media'])) {
            $prompt .= "Réseaux sociaux:\n";
            foreach ($data['social_media'] as $platform => $url) {
                $prompt .= "- {$platform}: {$url}\n";
            }
            $prompt .= "\n";
        }
        
        $prompt .= "Instructions spécifiques:\n";
        $prompt .= "1. Crée uniquement le code HTML et CSS pour une carte de visite moderne et professionnelle\n";
        $prompt .= "2. Utilise des dégradés de couleurs modernes et élégants\n";
        $prompt .= "3. Inclus des effets visuels subtils comme des ombres, des formes géométriques ou des motifs\n";
        $prompt .= "4. Assure-toi que le design est responsive et s'adapte à différentes tailles d'écran\n";
        $prompt .= "5. Utilise des polices modernes et lisibles\n";
        $prompt .= "6. Organise les informations de manière claire et professionnelle\n\n";
        $prompt .= "Réponds uniquement avec le code HTML et CSS de la carte de visite, sans explications. Le code doit être complet et prêt à être utilisé.";
        
        // Appeler l'API Gemini
        $response = $this->geminiApiService->getChatResponse($prompt);
        
        // Extraire le code HTML de la réponse
        return $this->extractHtmlFromResponse($response);
    }

    private function extractHtmlFromResponse(string $response): string
    {
        // Nettoyer la réponse pour extraire uniquement le code HTML
        // Supprimer les balises de code markdown si présentes
        $response = preg_replace('/```html\s*/', '', $response);
        $response = preg_replace('/```\s*/', '', $response);
        
        // Supprimer les explications textuelles avant ou après le code HTML
        if (strpos($response, '<!DOCTYPE html>') !== false) {
            $startPos = strpos($response, '<!DOCTYPE html>');
            $endPos = strrpos($response, '</html>') + 7;
            if ($startPos !== false && $endPos !== false) {
                $response = substr($response, $startPos, $endPos - $startPos);
            }
        }
        
        return trim($response);
    }
}

