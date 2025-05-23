<?php

namespace App\Controller\JobSeeker;

use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker', name: 'job_seeker_')]
class HomeController extends AbstractController
{
    public function __construct(
        private ConversationRepository $conversationRepository
    ) {
    }

    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $unreadCount = $this->conversationRepository->getUnreadCount($user);

        return $this->render('job_seeker/home.html.twig', [
            'stats' => [
                'jobs_applied' => 12,
                'interviews' => 3,
                'messages' => $unreadCount,
                'saved_jobs' => 8
            ],
            'recent_activities' => [
                [
                    'type' => 'application',
                    'title' => 'Applied for Senior Developer at TechCorp',
                    'time' => '2 hours ago'
                ],
                [
                    'type' => 'interview',
                    'title' => 'Interview scheduled with HR Manager',
                    'time' => 'Yesterday, 3:45 PM'
                ]
            ]
        ]);
    }
}