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
            ->leftJoin('channel.participants', 'channelPlayer')
            ->where($queryBuilder->expr()->eq('channelPlayer.participant', ':player'))
            ->setParameter('player', $player->getId())
        ;

        if (!$privateOnly) {
            $queryBuilder
                ->orWhere($queryBuilder->expr()->eq('channel.scope', ':public'))
                ->setParameter('public', ChannelScopeEnum::PUBLIC)
            ;
        }

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
