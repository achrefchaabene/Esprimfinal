<?php

namespace App\Twig;

use App\Repository\ConversationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $security;
    private $conversationRepository;

    public function __construct(Security $security, ConversationRepository $conversationRepository)
    {
        $this->security = $security;
        $this->conversationRepository = $conversationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'getUnreadMessagesCount']),
        ];
    }

    public function getUnreadMessagesCount(): int
    {
        $user = $this->security->getUser();
        if (!$user) {
            return 0;
        }

        return $this->conversationRepository->getUnreadCount($user);
    }
}




