<?php

namespace Mush\Chat\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Chat\Entity\Channel;
use Mush\Chat\Entity\Message;
use Mush\Chat\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Enum\RoomEventEnum;

/**
 * @template-extends ServiceEntityRepository<Message>
 */
final class MessageRepository extends ServiceEntityRepository implements MessageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findNeronCycleReport(Daedalus $daedalus, array $eventTags): ?Message
    {
        $queryBuilder = $this->createQueryBuilder('message');

        /**
         * @HACK : add a big tolerance to the cycle start to avoid taking the previous cycle report
         * Yes, this is an embarassing and not mastered way to do this, but I am a bad programmer doing my best
         */
        $cycleChange = \in_array(EventEnum::NEW_CYCLE, $eventTags, true);
        $propagatingFire = \in_array(RoomEventEnum::PROPAGATING_FIRE, $eventTags, true);

        if ($cycleChange || $propagatingFire) {
            $cycleStartedAt = clone $daedalus->getCycleStartedAt();
            $offset = ($daedalus->getGameConfig()->getDaedalusConfig()->getCycleLength() * 60 - 1);
            $cycleStartedAt->modify("+{$offset} seconds");
        } else {
            $cycleStartedAt = $daedalus->getCycleStartedAt();
        }

        $queryBuilder
            ->where($queryBuilder->expr()->eq('message.neron', ':neron'))
            ->andWhere($queryBuilder->expr()->gte('message.createdAt', ':cycleStart'))
            ->andWhere($queryBuilder->expr()->eq('message.message', ':failureMessage'))
            ->setParameter('neron', $daedalus->getDaedalusInfo()->getNeron()->getId())
            ->setParameter('cycleStart', $cycleStartedAt)
            ->setParameter('failureMessage', NeronMessageEnum::CYCLE_FAILURES);

        $results = $queryBuilder->getQuery()->getResult();

        if (\count($results) === 0) {
            return null;
        }

        return current($results);
    }

    public function findByChannel(Channel $channel, ?\DateInterval $ageLimit = null): array
    {
        $queryBuilder = $this->createQueryBuilder('message');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('message.channel', ':channel'))
            ->andWhere($queryBuilder->expr()->isNull('message.parent'))
            ->setParameter('channel', $channel);

        if ($ageLimit !== null) {
            $timeLimit = new \DateTime();
            $timeLimit->sub($ageLimit);

            $queryBuilder
                ->andWhere($queryBuilder->expr()->gte('message.updatedAt', ':date'))
                ->setParameter('date', $timeLimit);
        }

        $queryBuilder->orderBy('message.updatedAt', 'desc');

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(Message $message): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($message);
        $entityManager->flush();
    }

    public function saveAll(array $messages): void
    {
        $entityManager = $this->getEntityManager();

        try {
            $entityManager->beginTransaction();
            foreach ($messages as $message) {
                $entityManager->persist($message);
            }
            $entityManager->flush();
            $entityManager->commit();
        } catch (\Throwable $e) {
            $entityManager->rollback();
            $entityManager->close();

            throw $e;
        }
    }

    public function findById(int $id): ?Message
    {
        return $this->find($id);
    }
}
