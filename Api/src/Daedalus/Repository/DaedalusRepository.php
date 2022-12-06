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

    public function existAvailableDaedalus(): bool
    {
        $qb = $this->createQueryBuilder('daedalus');

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->groupBy('daedalus')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->having('count(player) < ' . 16)
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
        ;

        return count($qb->getQuery()->getResult()) > 0;
    }

    public function findAvailableDaedalus(string $name): ?Daedalus
    {
        $qb = $this->createQueryBuilder('daedalus');

        $daedalusConfig = $this->createQueryBuilder('daedalusConfig');
        $daedalusConfig
            ->select('count(characterConfig.id)')
            ->from(GameConfig::class, 'config')
            ->leftJoin('config.charactersConfig', 'characterConfig')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->where($qb->expr()->eq('config.id', 'daedalus_info.gameConfig'))
        ;

        $qb
            ->select('daedalus')
            ->leftJoin('daedalus.players', 'player')
            ->leftJoin('daedalus.daedalusInfo', 'daedalus_info')
            ->andWhere($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->andWhere($qb->expr()->eq('daedalus_info.name', ':name'))
            ->groupBy('daedalus')
            ->having('count(player.id) < (' . $daedalusConfig->getDQL() . ')')
            ->setMaxResults(1)
            ->setParameter('name', $name)
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
