<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Participant;
use App\Entity\User;
use App\Repository\ConversationRepository;
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
use Symfony\Component\WebLink\Link;

/**
 * @Route("/conversations", name="conversation_")
 * @method User getUser()
 */
class ConversationController extends AbstractController
{
    const MESSAGE_ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createdAt', 'mine'];

    private UserRepository $userRepository;
    private ConversationRepository $conversationRepository;

    public function __construct(UserRepository $userRepository, ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/", name="collection", methods={"GET"})
     */
    public function list(Request $request, HubInterface $hub): Response
    {
        $user = $this->getUser();
        $user = $this->userRepository->find(1);
        $conversations = $this->conversationRepository->findByUser($user);

        $this->addLink($request, new Link('mercure', $hub->getUrl()));
        return $this->json($conversations);
    }

    /**
     * @Route("/{conversation}/messages", name="get", methods={"GET"})
     */
    public function index(Conversation $conversation, MessageRepository $messageRepository): Response
    {
        // TODO on security fixed
//        $this->denyAccessUnlessGranted('view', $conversation);
        $user = $this->getUser();
        $user = $this->userRepository->find(1);
        $messages = $messageRepository->findBy(['conversation' => $conversation], ['id' => 'ASC']);

        array_map(function (Message $message) use ($user) {
            $message->setMine($message->getUser()->getId() === $user->getId());
        }, $messages);

        return $this->json($messages, Response::HTTP_OK, [], [
            'attributes' => self::MESSAGE_ATTRIBUTES_TO_SERIALIZE
        ]);
    }


    /**
     * @Route("/{conversation}/messages", name="post", methods={"POST"})
     */
    public function new(
        Request                $request,
        Conversation           $conversation,
        EntityManagerInterface $manager,
        ParticipantRepository  $participantRepository,
        SerializerInterface    $serializer,
        UserRepository         $userRepository,
        MessageRepository      $messageRepository,
        HubInterface           $hub
    ): JsonResponse
    {
        $creator = $this->getUser();
        $creator = $userRepository->findOneBy(['id' => 1]);
        $content = $request->get('content');

        $message = (new Message())
            ->setUser($creator)
            ->setContent($content)
            ->setConversation($conversation);
        $conversation->setLastMessage($message);

        $messageRepository->add($message);
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
                'attributes' => ['id', 'content', 'createdAt', 'mine', 'conversation' => ['id']]
            ])
        );

        $message->setMine(true);
        $hub->publish($update);
        return $this->json($message, Response::HTTP_CREATED, [], ['attributes' => self::MESSAGE_ATTRIBUTES_TO_SERIALIZE]);
    }
}
