<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;

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
            ->select('count(characterConfig.id)')
            ->from(GameConfig::class, 'config')
            ->leftJoin('config.charactersConfig', 'characterConfig')
            ->where($qb->expr()->eq('config.id', 'daedalus.gameConfig'))
        ;

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->andWhere($qb->expr()->eq('daedalus.gameStatus', ':gameStatus'))
            ->groupBy('daedalus')
            ->having('count(player.id) < (' . $daedalusConfig->getDQL() . ')')
            ->setMaxResults(1)
            ->setParameter('gameStatus', GameStatusEnum::STARTING)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
