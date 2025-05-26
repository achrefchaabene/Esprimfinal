<?php

namespace App\Controller\JobSeeker;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/interview', name: 'job_seeker_interview_')]
class BehavioralQuestionController extends AbstractController
{
    #[Route('/behavioral-questions', name: 'behavioral_questions')]
    public function index(): Response
    {
        // Create a static array of behavioral questions
        $questions = [
            [
                'id' => 1,
                'text' => 'Tell me about a time when you had to deal with a difficult team member.',
                'category' => 'Conflict Resolution',
                'subCategory' => 'Conflict Resolution',
                'is_saved' => false
            ],
            [
                'id' => 2,
                'text' => 'Describe a situation where you had to make a difficult decision with limited information.',
                'category' => 'Decision Making',
                'subCategory' => 'Decision Making',
                'is_saved' => false
            ],
            [
                'id' => 3,
                'text' => 'Give an example of how you worked successfully as part of a team.',
                'category' => 'Teamwork',
                'subCategory' => 'Teamwork',
                'is_saved' => true
            ],
            [
                'id' => 4,
                'text' => 'Tell me about a time you failed and what you learned from it.',
                'category' => 'Learning & Growth',
                'subCategory' => 'Learning & Growth',
                'is_saved' => false
            ]
        ];
        
        // Render the template with the questions
        return $this->render('job_seeker/interview_behavioral_questions.html.twig', [
            'questions' => $questions
        ]);
    }

    #[Route('/behavioral-questions/{id}/practice', name: 'practice_question')]
    public function practiceQuestion(int $id, Request $request): Response
    {
        // Créer un tableau de questions statiques
        $questions = [
            1 => [
                'id' => 1,
                'text' => 'Tell me about a time when you had to deal with a difficult team member.',
                'category' => 'Conflict Resolution',
                'tips' => 'Focus on how you handled the situation professionally.',
                'example' => 'In my previous role...'
            ],
            2 => [
                'id' => 2,
                'text' => 'Describe a situation where you had to make a difficult decision with limited information.',
                'category' => 'Decision Making',
                'tips' => 'Emphasize your analytical approach.',
                'example' => 'During a product launch...'
            ],
            3 => [
                'id' => 3,
                'text' => 'Give an example of how you worked successfully as part of a team.',
                'category' => 'Teamwork',
                'tips' => 'Highlight your collaboration skills.',
                'example' => 'In a cross-functional project team...'
            ]
        ];
        
        // Vérifier si la question existe
        if (!isset($questions[$id])) {
            throw $this->createNotFoundException('Question not found');
        }
        
        $question = $questions[$id];
        
        // Traiter l'enregistrement de la pratique si c'est une requête POST
        if ($request->isMethod('POST')) {
            // Simuler l'enregistrement
            $this->addFlash('success', 'Practice session recorded successfully!');
            return $this->redirectToRoute('job_seeker_interview_behavioral_questions');
        }
        
        // Convertir le tableau en objet pour la compatibilité avec le template
        $questionObj = new \stdClass();
        foreach ($question as $key => $value) {
            $questionObj->$key = $value;
        }
        
        return $this->render('job_seeker/interview_practice_question.html.twig', [
            'question' => $questionObj
        ]);
    }

    #[Route('/saved-questions', name: 'saved_questions')]
    public function savedQuestions(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        // In a real implementation, you would fetch the user's saved questions
        // For now, we'll use static data
        $questions = [
            // Example saved questions (can be empty for now)
        ];
        
        return $this->render('job_seeker/interview_saved_questions.html.twig', [
            'questions' => $questions
        ]);
    }
}




