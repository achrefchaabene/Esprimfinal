<?php

namespace App\Controller\JobSeeker;

use App\Entity\BehavioralQuestion;
use App\Entity\PreparationProgress;
use App\Repository\BehavioralQuestionRepository;
use App\Repository\PreparationProgressRepository;
use App\Repository\TechnicalChallengeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/interviews', name: 'job_seeker_interviews_')]
class InterviewController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(PreparationProgressRepository $progressRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $progress = $progressRepo->findOrCreateForUser($this->getUser());
        
        return $this->render('job_seeker/interview_index.html.twig', [
            'progress' => $progress
        ]);
    }
    
    #[Route('/common-questions', name: 'common_questions')]
    public function commonQuestions(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        // Exemple de questions communes
        $questions = [
            [
                'text' => 'Tell me about yourself',
                'tips' => 'Keep it professional and relevant to the job. Focus on your experience, skills, and what makes you a good fit.',
                'exampleAnswer' => 'I\'m a software developer with 5 years of experience specializing in web applications...',
                'difficulty' => 'easy',
                'subCategory' => 'introduction'
            ],
            [
                'text' => 'Why do you want to work for our company?',
                'tips' => 'Research the company beforehand. Mention specific aspects of their culture, products, or mission that appeal to you.',
                'exampleAnswer' => 'I\'ve been following your company\'s innovative approach to sustainable technology...',
                'difficulty' => 'medium',
                'subCategory' => 'motivation'
            ],
            [
                'text' => 'Where do you see yourself in 5 years?',
                'tips' => 'Be honest but strategic. Show ambition while demonstrating commitment to the role and company.',
                'exampleAnswer' => 'In five years, I hope to have grown into a senior position where I can lead projects and mentor junior team members...',
                'difficulty' => 'medium',
                'subCategory' => 'career'
            ],
            [
                'text' => 'What is your greatest weakness?',
                'tips' => 'Choose a genuine weakness, but focus on how you\'re working to improve it.',
                'exampleAnswer' => 'I sometimes struggle with public speaking, but I\'ve been taking courses and volunteering for presentations to improve this skill...',
                'difficulty' => 'hard',
                'subCategory' => 'self-awareness'
            ],
            [
                'text' => 'Why should we hire you?',
                'tips' => 'Highlight your unique combination of skills and experience that make you the best fit for the role.',
                'exampleAnswer' => 'My combination of technical expertise in your required technologies and my experience leading agile teams makes me uniquely qualified...',
                'difficulty' => 'hard',
                'subCategory' => 'value'
            ]
        ];
        
        return $this->render('job_seeker/interview_common_questions.html.twig', [
            'questions' => $questions
        ]);
    }
    
    #[Route('/behavioral-questions', name: 'behavioral_questions')]
    public function behavioralQuestions(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        // For now, we'll use hardcoded questions
        $questions = [
            [
                'id' => 1,
                'text' => 'Tell me about a time when you had to deal with a difficult team member.',
                'subCategory' => 'Conflict Resolution',
                'difficulty' => 'medium',
                'tips' => 'Focus on how you handled the situation professionally.',
                'exampleAnswer' => 'In my previous role...',
                'is_saved' => false
            ],
            [
                'id' => 2,
                'text' => 'Describe a situation where you had to make a difficult decision with limited information.',
                'subCategory' => 'Decision Making',
                'difficulty' => 'medium',
                'tips' => 'Emphasize your analytical approach.',
                'exampleAnswer' => 'During a product launch...',
                'is_saved' => false
            ],
            [
                'id' => 3,
                'text' => 'Give an example of how you worked successfully as part of a team.',
                'subCategory' => 'Teamwork',
                'difficulty' => 'easy',
                'tips' => 'Highlight your collaboration skills.',
                'exampleAnswer' => 'In a cross-functional project team...',
                'is_saved' => true
            ],
            [
                'id' => 4,
                'text' => 'Describe a situation where you had to adapt to a significant change.',
                'subCategory' => 'Adaptability',
                'difficulty' => 'medium',
                'tips' => 'Show your flexibility and positive attitude.',
                'exampleAnswer' => 'When my company suddenly shifted to remote work...',
                'is_saved' => false
            ]
        ];
        
        // Assurez-vous que le template est correctement spécifié
        return $this->render('job_seeker/interview_behavioral_questions.html.twig', [
            'questions' => $questions
        ]);
    }
    
    #[Route('/technical-challenges', name: 'technical_challenges')]
    public function technicalChallenges(TechnicalChallengeRepository $challengeRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $challenges = $challengeRepo->findAll();
        
        return $this->render('job_seeker/interview_technical_challenges.html.twig', [
            'challenges' => $challenges
        ]);
    }
    
    #[Route('/technical-challenges/{id}', name: 'challenge_solve')]
    public function solveChallenge(int $id, TechnicalChallengeRepository $challengeRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $challenge = $challengeRepo->find($id);
        
        if (!$challenge) {
            throw $this->createNotFoundException('Challenge not found');
        }
        
        return $this->render('job_seeker/interview_challenge_solve.html.twig', [
            'challenge' => $challenge
        ]);
    }
    
    #[Route('/mock-interview', name: 'mock_interview')]
public function mockInterview(): Response
{
    $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
    
    return $this->render('job_seeker/interview_mock.html.twig');
}
    
    #[Route('/progress/update', name: 'update_progress', methods: ['POST'])]
    public function updateProgress(Request $request, EntityManagerInterface $em, PreparationProgressRepository $progressRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $progress = $progressRepo->findOrCreateForUser($this->getUser());
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['section']) && isset($data['completed'])) {
            switch ($data['section']) {
                case 'common':
                    $progress->setCommonQuestionsCompleted($data['completed']);
                    break;
                case 'behavioral':
                    $progress->setBehavioralQuestionsCompleted($data['completed']);
                    break;
                case 'technical':
                    $progress->setTechnicalChallengesCompleted($data['completed']);
                    break;
                case 'mock':
                    $progress->setMockInterviewCompleted($data['completed']);
                    break;
            }
            
            $em->persist($progress);
            $em->flush();
            
            return $this->json(['success' => true]);
        }
        
        return $this->json(['success' => false, 'message' => 'Invalid data'], 400);
    }
    #[Route('/company-research', name: 'company_research')]
    public function companyResearch(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/interview_company_research.html.twig');
    }
    #[Route('/salary-negotiation', name: 'salary_negotiation')]
    public function salaryNegotiation(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/interview_salary_negotiation.html.twig');
    }
    #[Route('/ai-interview', name: 'ai_interview')]
    public function aiInterview(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        // Récupérer les paramètres de l'URL
        $interviewType = $request->query->get('type', 'general');
        $difficulty = $request->query->get('difficulty', 'medium');
        $duration = $request->query->get('duration', 15);
        
        // Préparer les questions en fonction du type et de la difficulté
        $questions = $this->getAIInterviewQuestions($interviewType, $difficulty, $duration);
        
        return $this->render('job_seeker/interview_ai_session.html.twig', [
            'type' => $interviewType,
            'difficulty' => $difficulty,
            'duration' => $duration,
            'questions' => $questions
        ]);
    }
    #[Route('/self-recording', name: 'self_recording')]
    public function selfRecording(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/interview_self_recording.html.twig');
    }
    
    /**
     * Génère des questions pour l'interview AI en fonction des paramètres
     */
    private function getAIInterviewQuestions(string $type, string $difficulty, int $duration): array
    {
        // Nombre de questions basé sur la durée (environ 3 minutes par question)
        $questionCount = max(3, intval($duration / 3));
        
        // Questions de base par type
        $questionsByType = [
            'general' => [
                'Tell me about yourself.',
                'What are your greatest strengths?',
                'What are your greatest weaknesses?',
                'Why do you want to work for this company?',
                'Where do you see yourself in 5 years?',
                'Why should we hire you?',
                'What is your ideal work environment?',
                'How do you handle stress and pressure?',
                'What are your salary expectations?',
                'Do you have any questions for us?'
            ],
            'behavioral' => [
                'Tell me about a time you faced a conflict with a coworker.',
                'Describe a situation where you had to meet a tight deadline.',
                'Give an example of a goal you reached and how you achieved it.',
                'Tell me about a mistake you made and how you handled it.',
                'Describe a situation where you had to work with a difficult team member.',
                'Tell me about a time you went above and beyond at work.',
                'Give an example of how you set goals and achieve them.',
                'Describe a situation where you had to make a difficult decision.',
                'Tell me about a time you had to adapt to a significant change.',
                'Give an example of how you worked on a team.'
            ],
            'technical' => [
                'Explain your approach to problem-solving.',
                'How do you stay updated with the latest technologies?',
                'Describe a challenging technical problem you solved recently.',
                'How do you ensure code quality in your projects?',
                'Explain your experience with agile methodologies.',
                'How do you handle technical disagreements with team members?',
                'Describe your experience with version control systems.',
                'How do you approach testing and debugging?',
                'Explain a complex technical concept in simple terms.',
                'How do you balance technical debt with delivery deadlines?'
            ]
        ];
        
        // Sélectionner les questions en fonction du type
        $availableQuestions = $questionsByType[$type] ?? $questionsByType['general'];
        
        // Mélanger les questions et en prendre le nombre nécessaire
        shuffle($availableQuestions);
        $selectedQuestions = array_slice($availableQuestions, 0, $questionCount);
        
        // Formater les questions pour le template
        $formattedQuestions = [];
        foreach ($selectedQuestions as $index => $question) {
            $formattedQuestions[] = [
                'id' => $index + 1,
                'text' => $question,
                'difficulty' => $difficulty
            ];
        }
        
        return $formattedQuestions;
    }
}
