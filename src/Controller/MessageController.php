<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/message", name="message_")
 * @method User getUser()
 */
class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createdAt', 'mine'];
    /**
     * @var MessageRepository
     */
    private $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {

        $this->messageRepository = $messageRepository;
    }
    /**
     * @Route("/{conversation}", name="get", methods={"GET"})
     */
    public function index(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $messages = $this->messageRepository->findBy(['conversation' => $conversation], ['id' => 'DESC']);

        array_map(function (Message $message){
            $message->setMine($message->getUser()->getId() === $this->getUser()->getId());
        }, $messages);

        return $this->json($messages, Response::HTTP_OK, [], [
            'attributes' => self::ATTRIBUTES_TO_SERIALIZE
        ]);
    }

    /**
     * @Route("/{conversation}", name="new", methods={"POST"})
     */
    public function new(Request $request, Conversation $conversation, EntityManagerInterface $manager): JsonResponse
    {
        $content = $request->get('content');

        $message = (new Message())
            ->setUser($this->getUser())
            ->setContent($content)
            ->setConversation($conversation)
            ->setMine(true)
        ;
        $conversation->setLastMessage($message);

        $this->messageRepository->add($message);
        $manager->persist($conversation);
        $manager->flush();

        return $this->json($message, Response::HTTP_CREATED, [], ['attributes' => self::ATTRIBUTES_TO_SERIALIZE]);
    }
}
