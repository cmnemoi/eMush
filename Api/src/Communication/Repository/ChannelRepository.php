<?php

namespace Mush\Communication\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Communication\Entity\Channel;
use Mush\Player\Entity\Player;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function findByPlayer(Player $player): Collection
    {
        $queryBuilder = $this->createQueryBuilder('channel');
        $queryBuilder
            ->leftJoin('channel.participants', 'player')
            ->where($queryBuilder->expr()->eq('player', ':player'))
            ->setParameter('player', $player->getId())
        ;

        return new ArrayCollection($queryBuilder->getQuery()->getResult());
    }
}
