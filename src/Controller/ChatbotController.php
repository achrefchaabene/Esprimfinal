<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeminiApiService;
use Psr\Log\LoggerInterface;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'chatbot')]
    public function index(): Response
    {
        // Message de bienvenue initial
        $welcomeMessage = "Bonjour ! Je suis Tchala, votre assistant spécialisé dans les questions d'emploi, de stage et d'entretien. Posez-moi vos questions !";
        
        return $this->render('chatbot/index.html.twig', [
            'welcomeMessage' => $welcomeMessage
        ]);
    }

    #[Route('/chatbot/send', name: 'chatbot_send', methods: ['POST'])]
    public function send(Request $request, GeminiApiService $geminiAPI, LoggerInterface $logger): JsonResponse
    {
        try {
            // Log la requête entrante
            $logger->info('Requête chatbot reçue', [
                'content_type' => $request->headers->get('Content-Type'),
                'content_length' => $request->headers->get('Content-Length')
            ]);
            
            // Récupérer et valider les données
            $content = $request->getContent();
            $logger->info('Contenu de la requête', ['content' => $content]);
            
            $data = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $logger->error('JSON invalide reçu', [
                    'content' => $content,
                    'error' => json_last_error_msg()
                ]);
                return new JsonResponse(['error' => 'Format de données invalide: ' . json_last_error_msg()], 400);
            }
            
            $userMessage = trim($data['message'] ?? '');
            $logger->info('Message utilisateur', ['message' => $userMessage]);

            if (empty($userMessage)) {
                return new JsonResponse(['error' => 'Veuillez entrer un message.'], 400);
            }

            // Instructions pour le chatbot
            $guidance = <<<PROMPT
Tu es Tchala, un assistant conversationnel spécialisé dans les conseils pour les entretiens d'embauche, 
les offres d'emploi et les stages. Ta mission est d'aider les utilisateurs à préparer leurs entretiens, 
rédiger leurs CV et lettres de motivation, et donner des conseils professionnels.

Règles strictes à suivre :
1. Réponds uniquement aux questions liées au monde professionnel (emploi, stage, entretien)
2. Sois bienveillant, professionnel et encourageant
3. Structure tes réponses avec des paragraphes clairs et des listes à puces quand c'est pertinent
4. Si la question n'est pas dans ton domaine, réponds poliment en expliquant ta spécialité

Question de l'utilisateur : 
PROMPT;

            $fullPrompt = $guidance . $userMessage;
            
            $logger->info('Envoi du prompt à l\'API Gemini');
            $response = $geminiAPI->getChatResponse($fullPrompt);
            $logger->info('Réponse reçue de l\'API Gemini', ['response_length' => strlen($response)]);
            
            $formattedResponse = $this->formatResponse($response);
            
            return new JsonResponse([
                'response' => $formattedResponse
            ]);
        } catch (\Exception $e) {
            $logger->error('Erreur dans ChatbotController::send', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return new JsonResponse([
                'error' => 'Désolé, un problème technique est survenu: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatResponse(string $response): string
    {
        // Formater les listes pour le HTML
        $response = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $response);
        $response = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $response);
        $response = preg_replace('/- (.*?)(\n|$)/', '• $1<br>', $response);
        
        // Remplacer les sauts de ligne par des balises <br>
        $response = nl2br($response);
        
        return $response;
    }

    #[Route('/chatbot/test', name: 'chatbot_test')]
    public function test(GeminiApiService $geminiAPI, LoggerInterface $logger): Response
    {
        try {
            $testMessage = "Comment préparer un entretien d'embauche?";
            $logger->info('Test de l\'API Gemini', ['message' => $testMessage]);
            
            $response = $geminiAPI->getChatResponse($testMessage);
            $logger->info('Réponse de test reçue', ['response_length' => strlen($response)]);
            
            return new Response(
                '<html><body>
                    <h1>Test de l\'API Gemini</h1>
                    <h2>Message envoyé :</h2>
                    <pre>' . htmlspecialchars($testMessage) . '</pre>
                    <h2>Réponse brute :</h2>
                    <pre>' . htmlspecialchars($response) . '</pre>
                    <h2>Réponse formatée :</h2>
                    <div>' . $this->formatResponse($response) . '</div>
                </body></html>'
            );
        } catch (\Exception $e) {
            $logger->error('Erreur lors du test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return new Response(
                '<html><body>
                    <h1>Erreur lors du test de l\'API Gemini</h1>
                    <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
                    <h2>Trace :</h2>
                    <pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>
                </body></html>',
                500
            );
        }
    }
}




