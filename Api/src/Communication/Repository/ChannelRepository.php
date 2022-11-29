<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(PlayerInfo $playerInfo, bool $privateOnly = false): Collection
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':playerInfo'))
            ->setParameter('playerInfo', $playerInfo->getId())
        ;

        if (!$privateOnly) {
            $queryBuilder
                ->orWhere(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('channel.scope', ':public')
                    ))
                ->andWhere($queryBuilder->expr()->eq('channel.daedalus', ':daedalus'))
                ->setParameter('public', ChannelScopeEnum::PUBLIC)
                ->setParameter('daedalus', $player->getDaedalus())
            ;
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
