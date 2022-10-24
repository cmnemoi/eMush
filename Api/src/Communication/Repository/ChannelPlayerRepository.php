<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;

class ChannelPlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelPlayer::class);
    }

    public function findAvailablePlayerForPrivateChannel(Channel $channel, int $maxChannel): array
    {
        // Sub-query that gets all players that have more than $maxChannel private channel open
        $subQuery = $this->createQueryBuilder('sub_query');
        $subQuery
            ->select('IDENTITY(sub_query.participant)')
            ->join('sub_query.channel', 'channel')
            ->where($subQuery->expr()->eq('channel.scope', ':private'))
            ->andHaving($subQuery->expr()->gte('COUNT(channel.id)', ':maxChannel'))
            ->groupBy('sub_query.participant')
        ;

        // Sub-query2 that gets all players that are already in this channel
        $subQuery2 = $this->createQueryBuilder('sub_query_2');
        $subQuery2
            ->select('sub_2_player.id')
            ->join('sub_query_2.participant', 'sub_2_player')
            ->where($subQuery2->expr()->eq('sub_query_2.channel', ':currentChannel'))
        ;

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('player')
            ->from(Player::class, 'player')
            ->where($queryBuilder->expr()->eq('player.daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->eq('player.gameStatus', ':currentGameStatus'))
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
            ->setParameter('currentGameStatus', GameStatusEnum::CURRENT)
            ->setParameter('daedalus', $channel->getDaedalus())
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
