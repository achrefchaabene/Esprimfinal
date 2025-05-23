<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiApiService
{
    private $client;
    private $apiKey;
    private $logger;

    public function __construct(
        HttpClientInterface $client, 
        string $apiKey,
        LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->logger = $logger;
    }

    public function getChatResponse(string $message): string
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->apiKey;

            // Log la requête (sans la clé API)
            if ($this->logger) {
                $this->logger->info('Envoi de requête à Gemini API', [
                    'message_length' => strlen($message)
                ]);
            }

            $response = $this->client->request('POST', $url, [
                'json' => [
                    'contents' => [[
                        'parts' => [[ 'text' => $message ]]
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 2048,
                    ]
                ]
            ]);

            $data = $response->toArray(false);

            // Vérifier si la réponse contient les données attendues
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                if ($this->logger) {
                    $this->logger->error('Réponse Gemini API invalide', [
                        'response' => json_encode($data)
                    ]);
                }
                return 'Désolé, je n\'ai pas pu générer une réponse. Veuillez réessayer.';
            }

            return $data['candidates'][0]['content']['parts'][0]['text'];
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Erreur lors de l\'appel à Gemini API', [
                    'error' => $e->getMessage()
                ]);
            }
            return 'Une erreur est survenue lors de la communication avec l\'API. Détail: ' . $e->getMessage();
        }
    }
}
