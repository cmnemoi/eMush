<?php

namespace Mush\RoomLog\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\VisibilityEnum;

class RoomLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomLog::class);
    }

    public function getPlayerRoomLog(Player $player): array
    {
        $yesterday = new \DateTime('yesterday');

        $queryBuilder = $this->createQueryBuilder('roomLog');

        $queryBuilder
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('roomLog.room', ':room'),
                $queryBuilder->expr()->gte('roomLog.date', ':date'),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->eq('roomLog.visibility', ':public'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('roomLog.player', ':player'),
                        $queryBuilder->expr()->in('roomLog.visibility', ':privateArray'),
                    ),
                )
            ))
            ->orderBy('roomLog.date', 'desc')
            ->setParameter('room', $player->getRoom())
            ->setParameter('public', VisibilityEnum::PUBLIC)
            ->setParameter('privateArray', [VisibilityEnum::PRIVATE, VisibilityEnum::COVERT, VisibilityEnum::SECRET])
            ->setParameter('player', $player)
            ->setParameter('date', $yesterday)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
