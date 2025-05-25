<?php

namespace App\Controller\JobSeeker;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/interviews', name: 'job_seeker_interviews_')]
class CommonQuestionController extends AbstractController
{
    #[Route('/practice/{id}', name: 'practice_common_question')]
    public function practiceQuestion(string $id, Request $request): Response
    {
        // Convertir l'ID en entier si possible
        $idInt = intval($id);
        
        // Récupérer la question par son ID
        // Dans un environnement réel, vous récupéreriez la question depuis la base de données
        
        // Exemple de question statique pour démonstration
        $question = [
            'id' => $idInt,
            'text' => 'Question exemple #' . $idInt,
            'category' => 'Common',
            'tips' => 'Voici quelques conseils pour répondre à cette question...',
            'exampleAnswer' => 'Voici un exemple de réponse...'
        ];
        
        // Convertir le tableau en objet pour la compatibilité avec le template
        $questionObj = new \stdClass();
        foreach ($question as $key => $value) {
            $questionObj->$key = $value;
        }
        
        return $this->render('job_seeker/interview_practice_question.html.twig', [
            'question' => $questionObj
        ]);
    }
}
