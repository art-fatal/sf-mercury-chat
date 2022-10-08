<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/conversation", name="conversation_")
 * @method User getUser()
 */
class ConversationController extends AbstractController
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;

    public function __construct(UserRepository $userRepository, ConversationRepository $conversationRepository)
    {

        $this->userRepository = $userRepository;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/list", name="list", methods={"GET"})
     */
    public function list(): Response
    {
        $conversations = $this->conversationRepository->findByUser($this->getUser());

        return $this->json($conversations);
    }

    /**
     * @Route("/{user}", name="get", methods={"GET"})
     */
    public function getConversationWithUser(Request $request, User $user): Response
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
     * @Route("/{user}", name="new", methods={"POST"})
     */
    public function new(Request $request, User $user): Response
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
