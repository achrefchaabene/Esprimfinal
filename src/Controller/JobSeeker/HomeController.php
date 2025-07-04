<?php

namespace App\Controller\JobSeeker;

use App\Repository\ConversationRepository;
use App\Repository\ApplicationRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\InterviewRepository;
use App\Repository\SavedJobRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker', name: 'job_seeker_')]
class HomeController extends AbstractController
{
    public function __construct(
        private ConversationRepository $conversationRepository,
        private ApplicationRepository $applicationRepository,
        private JobApplicationRepository $jobApplicationRepository,
        private InterviewRepository $interviewRepository,
        private SavedJobRepository $savedJobRepository
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

        // Fetch real dynamic statistics
        $unreadCount = $this->conversationRepository->getUnreadCount($user);
        
        // Count applications (both Application and JobApplication entities)
        $applications = $this->applicationRepository->findByUser($user);
        $jobApplications = $this->jobApplicationRepository->findUserApplications($user);
        $totalApplications = count($applications) + count($jobApplications);
        
        // Count upcoming interviews
        $upcomingInterviews = $this->interviewRepository->findUpcomingForUser($user);
        $interviewsCount = count($upcomingInterviews);
        
        // Count saved jobs
        $savedJobs = $this->savedJobRepository->findByUser($user);
        $savedJobsCount = count($savedJobs);

        // Get recent activities
        $recentActivities = $this->getRecentActivities($user, $applications, $jobApplications, $upcomingInterviews);

        return $this->render('job_seeker/home.html.twig', [
            'stats' => [
                'jobs_applied' => $totalApplications,
                'interviews' => $interviewsCount,
                'messages' => $unreadCount,
                'saved_jobs' => $savedJobsCount
            ],
            'recent_activities' => $recentActivities,
            'upcoming_interviews' => $upcomingInterviews
        ]);
    }

    private function getRecentActivities($user, $applications, $jobApplications, $upcomingInterviews): array
    {
        $activities = [];

        // Add recent applications
        foreach (array_slice($applications, 0, 3) as $application) {
            $activities[] = [
                'type' => 'application',
                'title' => 'Applied for ' . ($application->getPublication() ? $application->getPublication()->getTitle() : 'a position'),
                'time' => $this->formatTimeAgo($application->getCreatedAt()),
                'icon' => 'fas fa-briefcase'
            ];
        }

        // Add recent job applications
        foreach (array_slice($jobApplications, 0, 2) as $jobApp) {
            $activities[] = [
                'type' => 'job_application',
                'title' => 'Applied for ' . ($jobApp->getJob() ? $jobApp->getJob()->getTitle() : 'a job position'),
                'time' => $this->formatTimeAgo($jobApp->getAppliedAt()),
                'icon' => 'fas fa-briefcase'
            ];
        }

        // Add upcoming interviews
        foreach (array_slice($upcomingInterviews, 0, 2) as $interview) {
            $activities[] = [
                'type' => 'interview',
                'title' => 'Upcoming ' . $interview->getType() . ' interview scheduled',
                'time' => $this->formatTimeAgo($interview->getScheduledAt()),
                'icon' => 'fas fa-comments'
            ];
        }

        // Sort by time (most recent first) and limit to 5 activities
        usort($activities, function($a, $b) {
            return strcmp($b['time'], $a['time']);
        });

        return array_slice($activities, 0, 5);
    }

    private function formatTimeAgo(\DateTimeInterface $date): string
    {
        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->days > 7) {
            return $date->format('M j, Y');
        } elseif ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        } else {
            return 'Just now';
        }
    }
}