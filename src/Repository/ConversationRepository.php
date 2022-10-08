<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Conversation>
 *
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Conversation[]    findAll()
 * @method Conversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    public function add(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Conversation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Conversation[] Returns an array of Conversation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Conversation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findByParticipants(?UserInterface $user, User $otherUser)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select($qb->expr()->count('p.conversation'))
            ->innerJoin('c.participants', 'p')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('p.user', $user->getId()),
                    $qb->expr()->eq('p.user', $otherUser->getId())
                )
            )
            ->groupBy('p.conversation')
            ->having(
                $qb->expr()->eq(
                    $qb->expr()->count('p.conversation'),
                    2
                )
            );
        return $qb->getQuery()
            ->getResult();
    }

    public function findByUser(User $user)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select('c.id as conversationId', 'otherUser.email', 'lastMessage.content', 'lastMessage.createdAt' )
            ->innerJoin('c.participants', 'p', Join::WITH, $qb->expr()->neq('p.user', ':user'))
            ->innerJoin('c.participants', 'me', Join::WITH, $qb->expr()->eq('me.user', ':user'))
            ->leftJoin('c.lastMessage', 'lastMessage')
            ->innerJoin('p.user', 'otherUser')
            ->innerJoin('me.user', 'meUser')
            ->andWhere('me.user = :user')
            ->setParameter('user', $user)
            ->orderBy('lastMessage.createdAt', 'DESC')
        ;

        return $qb->getQuery()
            ->getResult();
    }

    public function checkIfUserIsParticipant(?UserInterface $user, Conversation $conversation)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->innerJoin('c.participants', 'p')
            ->andWhere('c.id = :conversation')
            ->andWhere(
                $qb->expr()->eq('p.user', ':user')
            )
            ->setParameters([
                'conversation' => $conversation,
                'user' => $user
            ])
        ;

        return !!$qb->getQuery()
            ->getOneOrNullResult();
    }
}
