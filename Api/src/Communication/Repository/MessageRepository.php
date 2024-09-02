<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\Message;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Enum\RoomEventEnum;

/**
 * @template-extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
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
}
