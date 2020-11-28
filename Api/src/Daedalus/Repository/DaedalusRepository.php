<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

class DaedalusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Daedalus::class);
    }

    public function findAvailableDaedalus(): ?Daedalus
    {
        $qb = $this->createQueryBuilder('daedalus');

        $daedalusConfig = $this->createQueryBuilder('daedalusConfig');
        $daedalusConfig
            ->select('config.maxPlayer')
            ->from(GameConfig::class, 'config')
            ->where($qb->expr()->eq('config.id', 'daedalus.gameConfig'))
        ;
        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->leftJoin('daedalus.gameConfig', 'gameConfig')
            ->groupBy('daedalus')
            ->having('count(player.id) < (' . $daedalusConfig->getDQL() . ')')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
