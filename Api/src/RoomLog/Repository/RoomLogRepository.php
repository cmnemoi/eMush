<?php

namespace Mush\RoomLog\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;

/**
 * @template-extends ServiceEntityRepository<RoomLog>
 */
class RoomLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomLog::class);
    }

    /**
     * @psalm-suppress TooManyArguments
     */
    public function getPlayerRoomLog(PlayerInfo $playerInfo): array
    {
        $yesterday = new \DateTime('1 day ago');

        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        $queryBuilder = $this->createQueryBuilder('roomLog');

        $queryBuilder
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('roomLog.place', ':place'),
                $queryBuilder->expr()->gte('roomLog.date', ':date'),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in('roomLog.visibility', ':publicArray'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('roomLog.playerInfo', ':player'),
                        $queryBuilder->expr()->in('roomLog.visibility', ':privateArray'),
                    ),
                )
            ))
            ->orderBy('roomLog.date', 'desc')
            ->addOrderBy('roomLog.id', 'desc')
            ->setParameter('place', $player->getPlace())
            ->setParameter('publicArray', [VisibilityEnum::PUBLIC, VisibilityEnum::REVEALED])
            ->setParameter('privateArray', [VisibilityEnum::PRIVATE, VisibilityEnum::SECRET, VisibilityEnum::COVERT])
            ->setParameter('player', $playerInfo)
            ->setParameter('date', $yesterday)
        ;

        return $queryBuilder->getQuery()->getResult();
    }
}
