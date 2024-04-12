<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Entity\ChannelPlayer;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

/**
 * @template-extends ServiceEntityRepository<ChannelPlayer>
 */
class ChannelPlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChannelPlayer::class);
    }

    public function findAvailablePlayerForPrivateChannel(Channel $channel, Daedalus $daedalus, int $maxChannel): array
    {
        // Sub-query that gets all players that have more than $maxChannel private channel open
        $subQuery = $this->createQueryBuilder('sub_query');
        $subQuery
            ->select('IDENTITY(sub_query.participant)')
            ->join('sub_query.channel', 'channel')
            ->where($subQuery->expr()->eq('channel.scope', ':private'))
            ->andHaving($subQuery->expr()->gte('COUNT(channel.id)', ':maxChannel'))
            ->groupBy('sub_query.participant');

        // Sub-query2 that gets all players that are already in this channel
        $subQuery2 = $this->createQueryBuilder('sub_query_2');
        $subQuery2
            ->select('sub_2_player.id')
            ->join('sub_query_2.participant', 'sub_2_player')
            ->where($subQuery2->expr()->eq('sub_query_2.channel', ':currentChannel'));

        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $queryBuilder
            ->select('playerInfo')
            ->from(PlayerInfo::class, 'playerInfo')
            ->leftJoin(Player::class, 'game_player', Join::WITH, 'playerInfo.player = game_player')
            ->where($queryBuilder->expr()->eq('game_player.daedalus', ':daedalus'))
            ->andWhere($queryBuilder->expr()->notIn(
                'playerInfo.id',
                $subQuery->getDQL()
            ))
            ->andWhere($queryBuilder->expr()->notIn(
                'playerInfo.id',
                $subQuery2->getDQL()
            ))
            ->andWhere($queryBuilder->expr()->eq('playerInfo.gameStatus', ':gameStatus'))
            ->setParameter('private', ChannelScopeEnum::PRIVATE)
            ->setParameter('currentChannel', $channel)
            ->setParameter('maxChannel', $maxChannel)
            ->setParameter('daedalus', $daedalus)
            ->setParameter('gameStatus', GameStatusEnum::CURRENT); // only alive players should be able to join a channel

        return $queryBuilder->getQuery()->getResult();
    }
}
