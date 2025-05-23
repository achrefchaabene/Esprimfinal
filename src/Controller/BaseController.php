<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;

abstract class BaseController extends AbstractController
{
    protected $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    protected function renderWithConversations(string $view, array $parameters = [], HubInterface $hub = null): Response
    {
        $user = $this->getUser();
        
        if ($user) {
            // Ajouter les statistiques pour l'utilisateur connecté
            $unreadCount = $this->conversationRepository->getUnreadCount($user);
            
            $parameters['stats'] = [
                'messages' => $unreadCount
            ];
        }
        
        return $this->render($view, $parameters);
    }
}


