<?php

namespace App\Controller\JobSeeker;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Form\MessageType;
use App\Form\NewConversationType;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/job-seeker/messages', name: 'job_seeker_messages_')]
class MessagesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ConversationRepository $conversationRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $conversations = $conversationRepo->findByParticipant($this->getUser());
        
        return $this->render('job_seeker/messages.html.twig', [
            'conversations' => $conversations,
            'active_conversation' => null,
            'unread_counts' => $this->getUnreadCounts($conversations)
        ]);
    }

    #[Route('/conversation/{id}', name: 'conversation')]
    public function conversation(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        if (!$conversation->getParticipants()->contains($this->getUser())) {
            throw new AccessDeniedException('You are not a participant of this conversation');
        }

        // Marquer les messages comme lus
        $this->markMessagesAsRead($conversation, $em);

        $message = new Message();
        $message->setSender($this->getUser());
        $message->setConversation($conversation);
        
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $conversation->setUpdatedAt(new \DateTime());
            $em->flush();
            
            $this->addFlash('success', 'Message sent successfully');
            return $this->redirectToRoute('job_seeker_messages_conversation', ['id' => $conversation->getId()]);
        }
        
        $conversations = $conversationRepo->findByParticipant($this->getUser());
        
        return $this->render('job_seeker/messages.html.twig', [
            'conversations' => $conversations,
            'active_conversation' => $conversation,
            'form' => $form->createView(),
            'unread_counts' => $this->getUnreadCounts($conversations)
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        $conversation = new Conversation();
        $form = $this->createForm(NewConversationType::class, $conversation, [
            'current_user' => $this->getUser()
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter automatiquement l'utilisateur courant comme participant
            $conversation->addParticipant($this->getUser());
            
            $em->persist($conversation);
            $em->flush();
            
            $this->addFlash('success', 'Conversation created successfully');
            return $this->redirectToRoute('job_seeker_messages_conversation', ['id' => $conversation->getId()]);
        }
        
        return $this->render('job_seeker/new_conversation.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/archive/{id}', name: 'archive')]
    public function archive(Conversation $conversation, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_JOB_SEEKER');
        
        if (!$conversation->getParticipants()->contains($this->getUser())) {
            throw new AccessDeniedException('You are not a participant of this conversation');
        }

        $conversation->setIsArchived(true);
        $em->flush();

        $this->addFlash('success', 'Conversation archived');
        return $this->redirectToRoute('job_seeker_messages_index');
    }

    private function markMessagesAsRead(Conversation $conversation, EntityManagerInterface $em): void
    {
        foreach ($conversation->getMessages() as $message) {
            if ($message->getSender() !== $this->getUser() && !$message->getIsRead()) {
                $message->setIsRead(true);
            }
        }
        $em->flush();
    }

    private function getUnreadCounts(array $conversations): array
    {
        $unreadCounts = [];
        foreach ($conversations as $conversation) {
            $unreadCounts[$conversation->getId()] = $conversation->getUnreadCount($this->getUser());
        }
        return $unreadCounts;
    }
}
