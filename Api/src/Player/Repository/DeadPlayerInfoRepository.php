<?php

namespace Mush\Player\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;

class DeadPlayerInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeadPlayerInfo::class);
    }

    public function findOneByPlayer(Player $player): ?DeadPlayerInfo
    {
        $qb = $this->createQueryBuilder('dead_player_info');

        $qb
            ->where($qb->expr()->eq('player', ':player'))
            ->setParameter('player', $player)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
