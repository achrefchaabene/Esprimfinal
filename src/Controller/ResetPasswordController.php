<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use App\Form\ResetPasswordRequestFormType;
use App\Entity\User;
use App\Service\ResetPasswordEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;
    private $emailSender;
    private $entityManager;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper, 
        ResetPasswordEmail $emailSender,
        EntityManagerInterface $entityManager
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->emailSender = $emailSender;
        $this->entityManager = $entityManager;
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Process the form submission
            $email = $form->get('email')->getData();
            
            // Look for a user with that email
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $email,
            ]);
            
            // Do not reveal whether a user account was found or not
            if ($user) {
                try {
                    $resetToken = $this->resetPasswordHelper->generateResetToken($user);
                    
                    // Ajoutez cette ligne pour déboguer
                    $this->addFlash('debug', 'Token généré avec succès: ' . $resetToken->getToken());
                    
                    try {
                        // Enveloppez l'envoi d'email dans un bloc try/catch séparé
                        $this->emailSender->send(
                            $email,
                            $resetToken->getToken()
                        );
                        $this->addFlash('debug', 'Email envoyé avec succès');
                    } catch (\Exception $emailError) {
                        // Log l'erreur spécifique à l'envoi d'email
                        $this->addFlash('reset_password_error', 'Erreur d\'envoi d\'email: ' . $emailError->getMessage());
                        return $this->redirectToRoute('app_forgot_password_request');
                    }
                    
                    return $this->redirectToRoute('app_check_email');
                } catch (\Exception $e) {
                    // Si une erreur se produit lors de la génération du token
                    $this->addFlash('reset_password_error', 'Erreur: ' . $e->getMessage());
                    
                    return $this->redirectToRoute('app_forgot_password_request');
                }
            }
            
            // Redirect to check-email page even if user does not exist
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        // Nous ne voulons pas que les utilisateurs puissent visiter cette page directement
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('app_forgot_password_request');
        }
        
        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token = null, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($token) {
            // Nous stockons le token en session et le supprimons de l'URL pour éviter que l'URL soit
            // chargée dans un navigateur et potentiellement divulguée à des scripts tiers.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (\Exception $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getMessage()
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // Le token est valide; permettre à l'utilisateur de changer son mot de passe.
        if ($request->isMethod('POST')) {
            $plainPassword = $request->request->get('plainPassword');
            
            // Encode(hash) le mot de passe et le définir pour l'utilisateur
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $plainPassword
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // La session est nettoyée après la réinitialisation du mot de passe
            $this->resetPasswordHelper->removeResetRequest($token);

            $this->addFlash('success', 'Your password has been reset successfully. You can now log in with the new password.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'token' => $token,
        ]);
    }
}


