<?php
namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class ResetPasswordEmail
{
    private $mailer;
    private $tokenLifetime;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        // Récupérer la durée de vie du token depuis la configuration
        $this->tokenLifetime = 3600; // 1 heure par défaut
    }

    public function send(string $toEmail, string $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@yourdomain.com', 'Password Reset'))
            ->to($toEmail)
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => new \DateTime('+' . $this->tokenLifetime . ' seconds'),
            ]);

        $this->mailer->send($email);
    }
}
