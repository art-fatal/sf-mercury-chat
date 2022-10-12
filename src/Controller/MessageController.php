<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Participant;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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

        array_map(function (Message $message) {
            $message->setMine($message->getUser()->getId() === $this->getUser()->getId());
        }, $messages);

        return $this->json($messages, Response::HTTP_OK, [], [
            'attributes' => self::ATTRIBUTES_TO_SERIALIZE
        ]);
    }

    /**
     * @Route("/{conversation}", name="new", methods={"POST"})
     */
    public function new(
        Request                $request,
        Conversation           $conversation,
        EntityManagerInterface $manager,
        ParticipantRepository  $participantRepository,
        SerializerInterface    $serializer,
        UserRepository         $userRepository,
        HubInterface           $hub
    ): JsonResponse
    {
        $creator = $this->getUser();
//        $creator = $userRepository->findOneBy(['id' => 1]);
        $content = $request->get('content');

        $message = (new Message())
            ->setUser($creator)
            ->setContent($content)
            ->setConversation($conversation);
        $conversation->setLastMessage($message);

        $this->messageRepository->add($message);
        $manager->persist($conversation);
        $manager->flush();

        $topics = [
            "/conversations/{$conversation->getId()}",
        ];
        $recipients = $participantRepository->findByConversationAndUser($conversation, $creator);
        foreach ($recipients as $recipient) {
            $topics[] = "/conversations/{$recipient->getUser()->getUsername()}";
        }
        $message->setMine(false);

        $update = new Update(
            $topics,
            $serializer->serialize($message, 'json', [
                'attributes' => ['id', 'content','createdAt','mine','conversation' => ['id'] ]
            ])
        );

        $message->setMine(true);
        $hub->publish($update);
        return $this->json($message, Response::HTTP_CREATED, [], ['attributes' => self::ATTRIBUTES_TO_SERIALIZE]);
    }
}
