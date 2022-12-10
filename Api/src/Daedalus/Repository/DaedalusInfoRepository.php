<?php

namespace Mush\Daedalus\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Enum\GameStatusEnum;

/**
 * @template-extends ServiceEntityRepository<DaedalusInfo>
 */
class DaedalusInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DaedalusInfo::class);
    }

    public function findAvailableDaedalus(string $name): ?DaedalusInfo
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':game_status'))
            ->andWhere($qb->expr()->eq('daedalus_info.name', ':name'))
            ->setMaxResults(1)
            ->setParameter('name', $name)
            ->setParameter('game_status', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function existAvailableDaedalus(): bool
    {
        $qb = $this->createQueryBuilder('daedalus_info');

        $qb
            ->select('daedalus_info')
            ->where($qb->expr()->in('daedalus_info.gameStatus', ':gameStatus'))
            ->setParameter('gameStatus', [GameStatusEnum::STARTING, GameStatusEnum::STANDBY])
        ;

        return count($qb->getQuery()->getResult()) > 0;
    }
}
