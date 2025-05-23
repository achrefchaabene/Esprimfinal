<?php

// src/Controller/ChatController.php
// src/Controller/ChatController.php
namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Service\VoiceRecorder;
use App\Service\VoicePlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chat')]
class ChatController extends AbstractController
{
    private $security;
    private $em;
    private $voiceRecorder;
    private $voicePlayer;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        VoiceRecorder $voiceRecorder,
        VoicePlayer $voicePlayer
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->voiceRecorder = $voiceRecorder;
        $this->voicePlayer = $voicePlayer;
    }

    #[Route('/', name: 'chat_index', methods: ['GET'])]
    public function index(): Response
    {
        $messages = $this->em->getRepository(Message::class)->findLatestMessages();
        $form = $this->createForm(MessageType::class);

        return $this->render('chat/index.html.twig', [
            'messages' => $messages,
            'form' => $form->createView(),
            'welcome_message' => 'Bienvenue dans le chat!'
        ]);
    }

    // #[Route('/send', name: 'chat_send', methods: ['POST'])]
    // public function send(Request $request): JsonResponse
    // {
    //     $user = $this->security->getUser();
    //     if (!$user) {
    //         return new JsonResponse(['error' => 'Unauthorized'], 401);
    //     }

    //     $data = json_decode($request->getContent(), true);
    //     $message = new Message();
    //     $message->setSender($user->getUsername());

    //     if (isset($data['audio']) && $data['audio']) {
    //         // Gestion message vocal
    //         $audioData = base64_decode($data['audio']);
    //         $fileName = 'voice_'.uniqid().'.wav';
    //         file_put_contents($this->getParameter('voice_messages_directory').'/'.$fileName, $audioData);

    //         $message->setVoiceFilePath($fileName);
    //         $message->setContent('[Message vocal]');
    //     } elseif (isset($data['text']) && !empty(trim($data['text']))) {
    //         // Gestion message texte
    //         $message->setContent(trim($data['text']));
    //     } else {
    //         return new JsonResponse(['error' => 'Invalid message data'], 400);
    //     }

    //     $this->em->persist($message);
    //     $this->em->flush();

    //     return new JsonResponse([
    //         'status' => 'success',
    //         'message' => $this->formatMessage($message)
    //     ]);
    // }

    // #[Route('/messages', name: 'chat_get_messages', methods: ['GET'])]
    // public function getMessages(): JsonResponse
    // {
    //     $messages = $this->em->getRepository(Message::class)->findLatestMessages();
        
    //     $formattedMessages = array_map([$this, 'formatMessage'], $messages);

    //     return new JsonResponse($formattedMessages);
    // }

    // #[Route('/play/{filename}', name: 'chat_play_voice', methods: ['GET'])]
    // public function playVoiceMessage(string $filename): Response
    // {
    //     return $this->voicePlayer->play($filename);
    // }

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->getId(),
            'sender' => $message->getSender(),
            'content' => $message->getContent(),
            'voiceFilePath' => $message->getVoiceFilePath(),
            'isVoice' => $message->isVoiceMessage(),
            // 'timestamp' => $message->getCreatedAt()->format('H:i'),
            // 'date' => $message->getCreatedAt()->format('d/m/Y')
        ];
    }
}