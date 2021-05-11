<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Player\Entity\Player;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(Player $player, bool $privateOnly = false): Collection
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'player')
            ->where($queryBuilder->expr()->eq('player', ':player'))
            ->setParameter('player', $player->getId())
        ;

        if ($privateOnly) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->eq('channel.scope', ':private'))
                ->setParameter('private', ChannelScopeEnum::PRIVATE)
            ;
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }

    public function findAvailablePlayerForPrivateChannel(Channel $channel, int $maxChannel): array
    {
        //Sub-query that gets all players that have more than $maxChannel private channel open
        $subQuery = $this->createQueryBuilder('channel');
        $subQuery
            ->select('participant.id')
            ->where($subQuery->expr()->eq('channel.scope', ':private'))
            ->andWhere($subQuery->expr()->eq('participant.daedalus', ':daedalus'))
            ->andHaving($subQuery->expr()->gte('COUNT(channel.id)', ':maxChannel'))
            ->innerJoin('channel.participants', 'participant')
            ->groupBy('participant.id')
        ;

        //Sub-query2 that gets all players that are already in this channel
        $subQuery2 = $this->createQueryBuilder('channel2');
        $subQuery2
            ->select('participant2.id')
            ->innerJoin('channel2.participants', 'participant2')
            ->where($subQuery2->expr()->eq('channel2', ':currentChannel'))
        ;

        $queryBuilder = $this->createQueryBuilder('main');

        $queryBuilder
            ->select('player')
            ->from(Player::class, 'player')
            ->where($queryBuilder->expr()->eq('player.daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->notIn(
                'player.id',
                $subQuery->getDQL()
            ))
            ->andWhere($queryBuilder->expr()->notIn(
                'player.id',
                $subQuery2->getDQL()
            ))
            ->setParameter('private', ChannelScopeEnum::PRIVATE)
            ->setParameter('currentChannel', $channel)
            ->setParameter('maxChannel', $maxChannel)
            ->setParameter('daedalus', $channel->getDaedalus())
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
