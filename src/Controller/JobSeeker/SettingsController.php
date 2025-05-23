<?php

namespace App\Controller\JobSeeker;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\NotificationSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/job-seeker/settings', name: 'job_seeker_settings_')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        return $this->render('job_seeker/settings.html.twig');
    }

    #[Route('/account', name: 'account')]
    public function account(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $user = $this->getUser();
        $form = $this->createForm(AccountSettingsType::class, $user);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Account settings updated');
            return $this->redirectToRoute('job_seeker_settings_account');
        }
        
        return $this->render('job_seeker/settings_account.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/password', name: 'password')]
    public function password(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            if (!$passwordHasher->isPasswordValid($user, $data['current_password'])) {
                $this->addFlash('error', 'Current password is incorrect');
                return $this->redirectToRoute('job_seeker_settings_password');
            }
            
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $data['new_password']
                )
            );
            
            $em->flush();
            $this->addFlash('success', 'Password changed successfully');
            return $this->redirectToRoute('job_seeker_settings_password');
        }
        
        return $this->render('job_seeker/settings_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/notifications', name: 'notifications')]
    public function notifications(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $user = $this->getUser();
        $form = $this->createForm(NotificationSettingsType::class, $user);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Notification settings updated');
            return $this->redirectToRoute('job_seeker_settings_notifications');
        }
        
        return $this->render('job_seeker/settings_notifications.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete-account', name: 'delete_account')]
    public function deleteAccount(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            
            // In a real app, you would also need to handle:
            // - Deleting related data
            // - Logging out the user
            // - Sending confirmation email
            
            $em->remove($user);
            $em->flush();
            
            $this->addFlash('success', 'Your account has been deleted');
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('job_seeker/settings_delete_account.html.twig');
    }
}