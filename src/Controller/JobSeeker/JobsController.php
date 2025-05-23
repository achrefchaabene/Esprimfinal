<?php

namespace App\Controller\JobSeeker;

use App\Entity\Publication;
use App\Entity\SavedJob;
use App\Entity\Application;
use App\Form\JobSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ApplicationType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/job-seeker/jobs', name: 'job_seeker_jobs_')]
class JobsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Créer le formulaire de recherche
        $searchCriteria = [
            'keywords' => $request->query->get('search'),
            'category' => $request->query->get('category', 'all'),
            'industry' => $request->query->get('industry'),
            'address' => $request->query->get('address'),
            'dateFrom' => $request->query->get('dateFrom'),
            'dateTo' => $request->query->get('dateTo')
        ];
        
        // Créer le formulaire (vous devez créer ce type de formulaire)
        $form = $this->createForm(JobSearchType::class, null, [
            'method' => 'GET'
        ]);
        $form->handleRequest($request);
        
        // Récupération des publications
        if ($form->isSubmitted() && $form->isValid()) {
            // Utiliser les données du formulaire pour la recherche
            $formData = $form->getData();
            $publications = $em->getRepository(Publication::class)->advancedSearch($formData);
        } else {
            // Recherche simple ou affichage de toutes les publications
            $publications = $em->getRepository(Publication::class)->findBy(['isPublished' => true]);
        }
        
        // Récupération des candidatures pour chaque publication
        $applicationsByPublication = [];
        foreach ($publications as $publication) {
            if ($publication !== null && $publication->getId() !== null) {
                $applicationsByPublication[$publication->getId()] = 
                    $em->getRepository(Application::class)->findByPublication($publication->getId());
            }
        }
        
        return $this->render('job_seeker/job_search.html.twig', [
            'publications' => $publications,
            'applicationsByPublication' => $applicationsByPublication,
            'form' => $form->createView(),
            'searchCriteria' => $searchCriteria
        ]);
    }

    #[Route('/saved', name: 'saved')]
    public function saved(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $savedJobs = $em->getRepository(SavedJob::class)
            ->findBy(['user' => $this->getUser()]);
        
        return $this->render('job_seeker/saved_jobs.html.twig', [
            'savedJobs' => $savedJobs
        ]);
    }

    #[Route('/apply/{id}', name: 'apply')]
    public function apply(Publication $publication, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        // Vérifier si l'utilisateur a déjà postulé
        $existingApplication = $em->getRepository(Application::class)->findOneBy([
            'user' => $this->getUser(),
            'publication' => $publication
        ]);
        
        if ($existingApplication) {
            $this->addFlash('warning', 'Vous avez déjà postulé à cette offre');
            return $this->redirectToRoute('job_seeker_job_details', ['id' => $publication->getId()]);
        }
        
        $application = new Application();
        $application->setUser($this->getUser());
        $application->setPublication($publication);
        $application->setCreatedAt(new \DateTimeImmutable());
        $application->setStatus('pending');
        
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier CV
            $resumeFile = $form->get('resumeFile')->getData();
            if ($resumeFile) {
                $originalFilename = pathinfo($resumeFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$resumeFile->guessExtension();
                
                try {
                    $resumeFile->move(
                        $this->getParameter('resumes_directory'),
                        $newFilename
                    );
                    $application->setResumeFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de votre CV');
                }
            }
            
            $em->persist($application);
            $em->flush();
            
            $this->addFlash('success', 'Votre candidature a été envoyée avec succès');
            return $this->redirectToRoute('job_seeker_applications');
        }
        
        return $this->render('job_seeker/apply.html.twig', [
            'form' => $form->createView(),
            'publication' => $publication
        ]);
    }

    #[Route('/save/{id}', name: 'save')]
    public function save(Publication $publication, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $savedJob = new SavedJob();
        $savedJob->setUser($this->getUser());
        $savedJob->setPublication($publication);
        $savedJob->setSavedAt(new \DateTimeImmutable());
        
        $em->persist($savedJob);
        $em->flush();
        
        $this->addFlash('success', 'Job saved to your list');
        return $this->redirectToRoute('job_seeker_jobs_index');
    }

    #[Route('/applications', name: 'applications')]
    public function applications(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $applications = $em->getRepository(Application::class)
            ->findBy(['user' => $this->getUser()]);
        
        return $this->render('job_seeker/applications.html.twig', [
            'applications' => $applications
        ]);
    }
}
