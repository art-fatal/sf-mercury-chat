<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Participant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\WebLink\Link;

/**
 * @Route("/users", name="user_")
 * @method User getUser()
 */
class UserController extends AbstractController
{

    private ConversationRepository $conversationRepository;

    public function __construct(UserRepository $userRepository, ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/{user}/conversations", name="get", methods={"GET"})
     */
    public function getConversation(Request $request, User $user): Response
    {
        $conversation = $this->conversationRepository->findByUser($this->getUser(), $user);

        if (count($conversation)) {

            return $this->json([
                'id' => $conversation->getId(),
            ], Response::HTTP_CREATED, [], []);
        } else {
            throw new \Exception("Conversation Not Found");
        }

    }


    /**
     * @Route("/{user}/conversations", name="post", methods={"POST"})
     */
    public function postConversation(Request $request, User $user): Response
    {

        if ($user->getId() == $this->getUser()->getId()) {
            throw new \Exception("That's deep but you cannot create a conversation with yourself");
        }

        $conversation = $this->conversationRepository->findByUser($this->getUser(), $user);

        if (count($conversation)) {
            throw new \Exception("The conversation already exists");
        }


        $conversation = (new Conversation())
            ->addParticipant((new Participant())->setUser($user))
            ->addParticipant((new Participant())->setUser($this->getUser()));

        $this->conversationRepository->add($conversation, true);

        return $this->json([
            'id' => $conversation->getId(),
        ], Response::HTTP_CREATED, [], []);
    }
}
