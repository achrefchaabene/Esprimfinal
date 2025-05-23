<?php

namespace App\Controller\JobSeeker;

use App\Entity\Interview;
use App\Entity\JobApplication;
use App\Form\InterviewPreparationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/interviews', name: 'job_seeker_interviews_')]
class InterviewController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $upcomingInterviews = $em->getRepository(Interview::class)
            ->findUpcomingForUser($this->getUser());
            
        $applications = $em->getRepository(JobApplication::class)
            ->findBy(['user' => $this->getUser()], ['appliedAt' => 'DESC']);
        
        return $this->render('job_seeker/interview_prep.html.twig', [
            'upcomingInterviews' => $upcomingInterviews,
            'applications' => $applications
        ]);
    }

    #[Route('/prepare/{id}', name: 'prepare')]
    public function prepare(JobApplication $application, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        $this->denyAccessUnlessGranted('VIEW', $application);
        
        $form = $this->createForm(InterviewPreparationType::class, $application);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Interview preparation saved');
            return $this->redirectToRoute('job_seeker_interviews_index');
        }
        
        return $this->render('job_seeker/interview_preparation.html.twig', [
            'application' => $application,
            'form' => $form->createView()
        ]);
    }

    #[Route('/resources', name: 'resources')]
    public function resources(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/interview_resources.html.twig', [
            'categories' => [
                'common_questions' => 'Common Questions',
                'technical_challenges' => 'Technical Challenges',
                'behavioral' => 'Behavioral Questions'
            ]
        ]);
    }
}