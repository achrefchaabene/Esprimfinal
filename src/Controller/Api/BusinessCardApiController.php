<?php

namespace App\Controller\Api;

use App\Service\BusinessCardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class BusinessCardApiController extends AbstractController
{
    private $businessCardService;

    public function __construct(BusinessCardService $businessCardService)
    {
        $this->businessCardService = $businessCardService;
    }

    #[Route('/businesscard/generate', name: 'api_businesscard_generate', methods: ['POST'])]
    public function generateCard(Request $request): JsonResponse
    {
        try {
            // Récupérer et décoder les données JSON
            $content = $request->getContent();
            $payload = json_decode($content, true);

            if (!$payload || !isset($payload['data'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Invalid JSON payload or missing data field'
                ], 400);
            }

            // Extraire les données
            $data = $payload['data'];
            $templateId = $payload['template_id'] ?? 'classic_blue';
            $outputFormat = $payload['output_format'] ?? 'html';

            // Valider les données requises
            if (!isset($data['name']) || !isset($data['company'])) {
                return $this->json([
                    'success' => false,
                    'error' => 'Missing required fields in data (name, company)'
                ], 400);
            }

            // Vérifier si le format de sortie est supporté
            if ($outputFormat !== 'html') {
                return $this->json([
                    'success' => false,
                    'error' => "Output format '{$outputFormat}' not yet supported"
                ], 400);
            }

            // Générer la carte de visite
            $cardHtml = $this->businessCardService->generateCard($data, $templateId);

            return $this->json([
                'success' => true,
                'card_html' => $cardHtml,
                'template_used' => $templateId
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
