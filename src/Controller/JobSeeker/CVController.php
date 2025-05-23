<?php

namespace App\Controller\JobSeeker;

use App\Entity\CV;
use App\Form\CVType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/cv', name: 'job_seeker_cv_')]
class CVController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $cvs = $em->getRepository(CV::class)->findBy(['user' => $this->getUser()]);
        
        return $this->render('job_seeker/cv_list.html.twig', [
            'cvs' => $cvs
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $cv = new CV();
        $cv->setUser($this->getUser());
        
        $form = $this->createForm(CVType::class, $cv);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($cv);
            $em->flush();
            
            $this->addFlash('success', 'CV created successfully');
            return $this->redirectToRoute('job_seeker_cv_index');
        }
        
        return $this->render('job_seeker/cv_builder.html.twig', [
            'form' => $form->createView(),
            'templates' => [
                'Modern Red', 'Classic', 'Creative'
            ]
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(CV $cv, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        $this->denyAccessUnlessGranted('EDIT', $cv);
        
        $form = $this->createForm(CVType::class, $cv);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'CV updated successfully');
            return $this->redirectToRoute('job_seeker_cv_index');
        }
        
        return $this->render('job_seeker/cv_builder.html.twig', [
            'form' => $form->createView(),
            'cv' => $cv,
            'templates' => [
                'Modern Red', 'Classic', 'Creative'
            ]
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    public function delete(CV $cv, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        $this->denyAccessUnlessGranted('DELETE', $cv);
        
        $em->remove($cv);
        $em->flush();
        
        $this->addFlash('success', 'CV deleted successfully');
        return $this->redirectToRoute('job_seeker_cv_index');
    }
}