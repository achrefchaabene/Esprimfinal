<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\ConversationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class UnreadMessagesListener implements EventSubscriberInterface
{
    private $twig;
    private $security;
    private $conversationRepository;

    public function __construct(
        Environment $twig,
        Security $security,
        ConversationRepository $conversationRepository
    ) {
        $this->twig = $twig;
        $this->security = $security;
        $this->conversationRepository = $conversationRepository;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $user = $this->security->getUser();
        
        if ($user instanceof User) {
            $unreadCount = $this->conversationRepository->getUnreadCount($user);
            $this->twig->addGlobal('unread_count', $unreadCount);
        } else {
            $this->twig->addGlobal('unread_count', 0);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}


