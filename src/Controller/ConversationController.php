<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Message\MercureUpdateMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/conversation')]
#[IsGranted('ROLE_USER')]
class ConversationController extends BaseController
{
    public function __construct(
        ConversationRepository $conversationRepository,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private AuthorizationCheckerInterface $authorizationChecker,
        private HubInterface $hub,
        private Security $security,
        private MessageBusInterface $messageBus
    ) {
        parent::__construct($conversationRepository);
    }

    #[Route('/', name: 'conversation_index', methods: ['GET'])]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $conversations = $this->conversationRepository->findByParticipant($user);
        
        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
            'search_query' => null, // Ajouter cette ligne
        ]);
    }

    protected function renderWithConversations(string $template, array $parameters = [], HubInterface $hub = null): Response
    {
        // Cette méthode n'est plus nécessaire car nous n'utilisons plus Mercure
        return $this->render($template, $parameters);
    }

    #[Route('/unread-count', name: 'conversation_unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $count = $user ? $this->conversationRepository->getUnreadCount($user) : 0;
        
        return $this->json(['count' => $count]);
    }

    #[Route('/new', name: 'conversation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HubInterface $hub): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Invalid user type');
        }

        if ($request->isMethod('POST')) {
            $title = $request->request->get('title');
            $participantIds = $request->request->all('participants');
            $initialMessageContent = $request->request->get('initial_message');
            
            if (empty($participantIds)) {
                $this->addFlash('error', 'Vous devez sélectionner au moins un participant');
                return $this->redirectToRoute('conversation_new');
            }

            if (empty($initialMessageContent)) {
                $this->addFlash('error', 'Veuillez saisir un message initial');
                return $this->redirectToRoute('conversation_new');
            }

            $participants = $this->userRepository->findBy(['id' => $participantIds]);
            
            // Créer la conversation
            $conversation = new Conversation();
            $conversation->setTitle($title);
            $conversation->addParticipant($user); // Ajouter l'utilisateur courant
            
            foreach ($participants as $participant) {
                $conversation->addParticipant($participant);
            }
            
            $this->entityManager->persist($conversation);
            
            // Créer le message initial
            $message = new Message();
            $message->setContent($initialMessageContent);
            $message->setSender($user);
            $message->setConversation($conversation);
            // La date de création est déjà définie dans le constructeur
            $message->setIsRead(false);

            $this->entityManager->persist($message);
            $this->entityManager->flush();
            
            // Publier une mise à jour via Mercure pour notifier les participants
            $this->publishMessageUpdate($conversation, $message, $user);
            
            $this->addFlash('success', 'Conversation créée avec succès');
            return $this->redirectToRoute('conversation_show', ['id' => $conversation->getId()]);
        }

        // Récupérer tous les chercheurs d'emploi (sauf l'utilisateur courant)
        $users = $this->userRepository->findJobSeekers($user);
        
        return $this->renderWithConversations('conversation/new.html.twig', [
            'users' => $users,
        ], $hub);
    }

    #[Route('/{id}', name: 'conversation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, string $id): Response
    {
        // Convertir l'ID en entier
        $id = (int) $id;
        
        // Rechercher la conversation manuellement au lieu d'utiliser le ParamConverter
        $conversation = $this->conversationRepository->find($id);
        
        // Vérifier si la conversation existe
        if (!$conversation) {
            $this->addFlash('error', 'La conversation demandée n\'existe pas.');
            return $this->redirectToRoute('conversation_index');
        }
        
        // Vérifier que l'utilisateur est un participant de la conversation
        if (!$conversation->getParticipants()->contains($this->getUser())) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à accéder à cette conversation');
        }
        
        // Traitement du formulaire d'envoi de message
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            
            if (!empty($content)) {
                $message = new Message();
                $message->setContent($content);
                $message->setSender($this->getUser());
                $message->setConversation($conversation);
                $message->setCreatedAt(new \DateTime());
                
                $this->entityManager->persist($message);
                $this->entityManager->flush();
                
                // Publier une mise à jour via Mercure
                $this->publishMessageUpdate($conversation, $message, $this->getUser());
                
                return $this->redirectToRoute('conversation_show', ['id' => $conversation->getId()]);
            }
        }
        
        return $this->render('conversation/show.html.twig', [
            'conversation' => $conversation,
        ]);
    }

    #[Route('/{id}/typing', name: 'conversation_typing', methods: ['POST'])]
    public function typing(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $conversation);

        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Invalid user type');
        }

        $update = new Update(
            "/conversation/{$conversation->getId()}",
            json_encode([
                'type' => 'typing',
                'user_id' => $user->getId(),
                'username' => $user->getUsername(),
                'timestamp' => time()
            ])
        );

        $this->hub->publish($update);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/add-participant', name: 'conversation_add_participant', methods: ['POST'])]
    public function addParticipant(Request $request, string $id): Response 
    {
        $id = (int) $id;
        $conversation = $this->conversationRepository->find($id);
        
        if (!$conversation) {
            $this->addFlash('error', 'La conversation demandée n\'existe pas.');
            return $this->redirectToRoute('conversation_index');
        }
        
        $this->denyAccessUnlessGranted('EDIT', $conversation);

        $userId = $request->request->get('user_id');
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé');
            return $this->redirectToRoute('conversation_show', ['id' => $conversation->getId()]);
        }

        if ($conversation->getParticipants()->contains($user)) {
            $this->addFlash('warning', 'Cet utilisateur fait déjà partie de la conversation');
            return $this->redirectToRoute('conversation_show', ['id' => $conversation->getId()]);
        }
        
        $conversation->addParticipant($user);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Participant ajouté avec succès');
        return $this->redirectToRoute('conversation_show', ['id' => $conversation->getId()]);
    }

    #[Route('/{id}/leave', name: 'conversation_leave', methods: ['POST'])]
    public function leave(string $id): Response
    {
        $id = (int) $id;
        $conversation = $this->conversationRepository->find($id);
        
        if (!$conversation) {
            $this->addFlash('error', 'La conversation demandée n\'existe pas.');
            return $this->redirectToRoute('conversation_index');
        }
        
        $this->denyAccessUnlessGranted('VIEW', $conversation);

        /** @var User $user */
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException('Invalid user type');
        }

        $conversation->removeParticipant($user);
        
        if ($conversation->getParticipants()->count() <= 1) {
            $this->entityManager->remove($conversation);
        }
        
        $this->entityManager->flush();

        return $this->redirectToRoute('conversation_index');
    }

    /**
     * Marque tous les messages non lus d'une conversation comme lus pour un utilisateur donné
     */
    private function markMessagesAsRead(Conversation $conversation, User $user): void
    {
        $unreadMessages = $conversation->getMessages()->filter(
            fn(Message $message) => 
                $message->getSender() !== $user && 
                !$message->getIsRead()
        );
        
        foreach ($unreadMessages as $message) {
            $message->setIsRead(true);
        }
        
        if (count($unreadMessages) > 0) {
            $this->entityManager->flush();
        }
    }

    private function publishMessageUpdate(Conversation $conversation, Message $message, User $sender): void
    {
        $data = json_encode([
            'type' => 'new_message',
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sender' => [
                'id' => $sender->getId(),
                'username' => $sender->getUsername(),
                'avatar' => $sender->getProfileImagePath() ?? 'default-avatar.png'
            ],
            'conversation_id' => $conversation->getId(),
            'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'unread_count' => $this->conversationRepository->getUnreadCount($sender)
        ]);

        $topics = [
            "/conversation/{$conversation->getId()}",
            "/user/{$sender->getId()}"
        ];

        // Envoyer la mise à jour via le système de messagerie
        $this->messageBus->dispatch(new MercureUpdateMessage($topics, $data, true));
    }

    #[Route('/search', name: 'conversation_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $query = $request->query->get('q', '');
        $user = $this->getUser();
        
        if (empty($query)) {
            return $this->redirectToRoute('conversation_index');
        }
        
        // Utiliser directement la méthode du repository pour la recherche
        $conversations = $this->conversationRepository->searchConversations($user, $query);
        
        // Préparer les messages correspondants pour l'affichage
        $matchingMessages = [];
        
        foreach ($conversations as $conversation) {
            $matchingMessages[$conversation->getId()] = [];
            
            foreach ($conversation->getMessages() as $message) {
                if (stripos($message->getContent(), $query) !== false) {
                    $matchingMessages[$conversation->getId()][] = $message;
                }
            }
        }
        
        return $this->render('conversation/index.html.twig', [
            'conversations' => $conversations,
            'search_query' => $query,
            'matching_messages' => $matchingMessages
        ]);
    }

    #[Route('/{id}/archive', name: 'conversation_archive', methods: ['POST'])]
    public function archive(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $conversation);
        
        $conversation->setIsArchived(true);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Conversation archivée avec succès');
        return $this->redirectToRoute('conversation_index');
    }

    #[Route('/{id}/unarchive', name: 'conversation_unarchive', methods: ['POST'])]
    public function unarchive(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $conversation);
        
        $conversation->setIsArchived(false);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Conversation désarchivée avec succès');
        return $this->redirectToRoute('conversation_index');
    }

    #[Route('/archived', name: 'conversation_archived', methods: ['GET'])]
    public function archived(HubInterface $hub): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        $conversations = $this->conversationRepository->findArchivedByParticipant($user);
        
        return $this->renderWithConversations('conversation/archived.html.twig', [
            'conversations' => $conversations,
        ], $hub);
    }

    /**
     * Génère l'URL de souscription Mercure pour une conversation
     */
    private function generateMercureSubscribeUrl(HubInterface $hub, Conversation $conversation): string
    {
        $user = $this->getUser();
        $topics = [
            "/conversation/{$conversation->getId()}",
            "/user/{$user->getId()}"
        ];
        
        return $hub->getPublicUrl() . '?topic=' . urlencode(implode('&topic=', $topics));
    }

    #[Route('/{id}/check-new-messages', name: 'conversation_check_new_messages', methods: ['GET'])]
    public function checkNewMessages(Request $request, string $id): JsonResponse
    {
        $id = (int) $id;
        $conversation = $this->conversationRepository->find($id);
        
        if (!$conversation) {
            return $this->json(['error' => 'Conversation not found'], 404);
        }
        
        // Vérifier que l'utilisateur est un participant de la conversation
        if (!$conversation->getParticipants()->contains($this->getUser())) {
            return $this->json(['error' => 'Access denied'], 403);
        }
        
        $lastId = $request->query->get('lastId', 0);
        
        // Vérifier s'il y a des messages plus récents que lastId
        $hasNewMessages = $this->entityManager->getRepository(Message::class)
            ->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.conversation = :conversation')
            ->andWhere('m.id > :lastId')
            ->setParameter('conversation', $conversation)
            ->setParameter('lastId', $lastId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
        
        return $this->json(['hasNewMessages' => $hasNewMessages]);
    }

    #[Route('/debug-search', name: 'conversation_debug_search', methods: ['GET'])]
    public function debugSearch(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $query = $request->query->get('q', '');
        $user = $this->getUser();
        
        if (empty($query)) {
            return $this->json(['error' => 'Aucun terme de recherche fourni']);
        }
        
        // Rechercher toutes les conversations de l'utilisateur
        $allConversations = $this->conversationRepository->findByParticipant($user);
        
        // Filtrer manuellement les conversations qui contiennent le terme de recherche
        $result = [];
        
        foreach ($allConversations as $conversation) {
            $matchingMessages = [];
            $titleMatches = false;
            
            // Vérifier si le titre correspond
            if (stripos($conversation->getTitle(), $query) !== false) {
                $titleMatches = true;
            }
            
            // Vérifier si un message correspond
            foreach ($conversation->getMessages() as $message) {
                if (stripos($message->getContent(), $query) !== false) {
                    $matchingMessages[] = [
                        'id' => $message->getId(),
                        'content' => $message->getContent(),
                        'sender' => $message->getSender()->getUsername(),
                        'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }
            }
            
            if ($titleMatches || count($matchingMessages) > 0) {
                $result[] = [
                    'id' => $conversation->getId(),
                    'title' => $conversation->getTitle(),
                    'titleMatches' => $titleMatches,
                    'matchingMessages' => $matchingMessages,
                    'totalMessages' => count($matchingMessages)
                ];
            }
        }
        
        return $this->json([
            'query' => $query,
            'totalConversations' => count($result),
            'conversations' => $result
        ]);
    }

    #[Route('/debug-job-seeker-search', name: 'conversation_debug_job_seeker_search', methods: ['GET'])]
    public function debugJobSeekerSearch(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $query = $request->query->get('q', '');
        $user = $this->getUser();
        
        if (empty($query)) {
            return $this->json(['error' => 'Aucun terme de recherche fourni']);
        }
        
        // Rechercher les conversations
        $conversations = $this->conversationRepository->searchConversationsByJobSeekerMessages($user, $query);
        
        $result = [];
        foreach ($conversations as $conversation) {
            $jobSeekerMessages = [];
            $otherMessages = [];
            
            foreach ($conversation->getMessages() as $message) {
                $messageData = [
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'sender' => $message->getSender()->getUsername(),
                    'roles' => $message->getSender()->getRoles(),
                    'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                    'matchesQuery' => (stripos($message->getContent(), $query) !== false)
                ];
                
                if (in_array('ROLE_JOB_SEEKER', $message->getSender()->getRoles())) {
                    $jobSeekerMessages[] = $messageData;
                } else {
                    $otherMessages[] = $messageData;
                }
            }
            
            $result[] = [
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'titleMatchesQuery' => (stripos($conversation->getTitle(), $query) !== false),
                'participants' => array_map(function($p) use ($query) {
                    return [
                        'username' => $p->getUsername(),
                        'roles' => $p->getRoles(),
                        'isJobSeeker' => in_array('ROLE_JOB_SEEKER', $p->getRoles()),
                        'matchesQuery' => (
                            stripos($p->getUsername(), $query) !== false || 
                            (method_exists($p, 'getFirstName') && stripos($p->getFirstName(), $query) !== false) ||
                            (method_exists($p, 'getLastName') && stripos($p->getLastName(), $query) !== false)
                        )
                    ];
                }, $conversation->getParticipants()->toArray()),
                'jobSeekerMessages' => $jobSeekerMessages,
                'otherMessages' => $otherMessages
            ];
        }
        
        return $this->json([
            'query' => $query,
            'count' => count($conversations),
            'conversations' => $result
        ]);
    }

    #[Route('/test-search', name: 'conversation_test_search')]
    public function testSearch(Request $request): Response
    {
        $query = $request->query->get('q', 'test');
        $user = $this->getUser();
        
        // Récupérer toutes les conversations
        $conversations = $this->conversationRepository->findByParticipant($user);
        
        $result = [];
        foreach ($conversations as $conversation) {
            $messages = [];
            foreach ($conversation->getMessages() as $message) {
                $messages[] = [
                    'id' => $message->getId(),
                    'content' => $message->getContent(),
                    'sender' => $message->getSender()->getUsername(),
                    'matches' => (stripos($message->getContent(), $query) !== false)
                ];
            }
            
            $result[] = [
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'title_matches' => (stripos($conversation->getTitle(), $query) !== false),
                'messages' => $messages,
                'message_count' => count($messages),
                'matching_message_count' => count(array_filter($messages, function($m) { return $m['matches']; }))
            ];
        }
        
        return $this->json([
            'query' => $query,
            'user_id' => $user->getId(),
            'conversation_count' => count($conversations),
            'conversations' => $result
        ]);
    }

    #[Route('/diagnostic-search', name: 'conversation_diagnostic_search')]
    public function diagnosticSearch(Request $request): Response
    {
        $query = $request->query->get('q', '');
        $user = $this->getUser();
        
        // Récupérer toutes les conversations
        $conversations = $this->conversationRepository->findByParticipant($user);
        
        // Informations de diagnostic
        $diagnosticInfo = [
            'query' => $query,
            'user_id' => $user->getId(),
            'total_conversations' => count($conversations),
            'conversations' => []
        ];
        
        foreach ($conversations as $conversation) {
            $matchingMessages = [];
            
            foreach ($conversation->getMessages() as $message) {
                if (stripos($message->getContent(), $query) !== false) {
                    $matchingMessages[] = [
                        'id' => $message->getId(),
                        'content' => $message->getContent(),
                        'sender' => $message->getSender()->getUsername(),
                        'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }
            }
            
            $diagnosticInfo['conversations'][] = [
                'id' => $conversation->getId(),
                'title' => $conversation->getTitle(),
                'title_matches' => !empty($query) && stripos($conversation->getTitle(), $query) !== false,
                'participant_count' => $conversation->getParticipants()->count(),
                'message_count' => $conversation->getMessages()->count(),
                'matching_messages' => $matchingMessages,
                'matching_message_count' => count($matchingMessages)
            ];
        }
        
        return $this->json($diagnosticInfo);
    }
}






























