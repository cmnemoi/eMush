<?php

namespace Mush\RoomLog\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;

/**
 * @template-extends ServiceEntityRepository<RoomLog>
 */
final class RoomLogRepository extends ServiceEntityRepository implements RoomLogRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RoomLog::class);
    }

    /**
     * @psalm-suppress TooManyArguments
     */
    public function getPlayerRoomLog(PlayerInfo $playerInfo, \DateTime $limitDate = new \DateTime('1 day ago')): array
    {
        /** @var Player $player */
        $player = $playerInfo->getPlayer();

        $queryBuilder = $this->createQueryBuilder('roomLog');

        $queryBuilder
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('roomLog.daedalusInfo', ':daedalusInfo'),
                $queryBuilder->expr()->eq('roomLog.place', ':place'),
                $queryBuilder->expr()->gte('roomLog.createdAt', ':date'),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in('roomLog.visibility', ':publicArray'),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('roomLog.playerInfo', ':player'),
                        $queryBuilder->expr()->in('roomLog.visibility', ':privateArray'),
                    ),
                )
            ))
            ->orderBy('roomLog.createdAt', 'desc')
            ->addOrderBy('roomLog.id', 'desc')
            ->setParameter('daedalusInfo', $player->getDaedalus()->getDaedalusInfo())
            ->setParameter('place', $player->getPlace()->getName())
            ->setParameter('publicArray', [VisibilityEnum::PUBLIC, VisibilityEnum::REVEALED])
            ->setParameter('privateArray', [VisibilityEnum::PRIVATE, VisibilityEnum::SECRET, VisibilityEnum::COVERT])
            ->setParameter('player', $playerInfo)
            ->setParameter('date', $limitDate);

        return $queryBuilder->getQuery()->getResult();
    }

    public function getAllRoomLogsByDaedalus(Daedalus $daedalus): array
    {
        $queryBuilder = $this->createQueryBuilder('roomLog');

        $queryBuilder
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('roomLog.daedalusInfo', ':daedalusInfo'),
            ))
            ->addOrderBy('roomLog.id', 'desc')
            ->setParameter('daedalusInfo', $daedalus);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllByDaedalusAndPlace(Daedalus $daedalus, Place $place): array
    {
        $queryBuilder = $this->createQueryBuilder('roomLog');

        $queryBuilder
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('roomLog.daedalusInfo', ':daedalusInfo'),
                $queryBuilder->expr()->eq('roomLog.place', ':place')
            ))
            ->setParameter('daedalusInfo', $daedalus->getDaedalusInfo())
            ->setParameter('place', $place->getName());

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(RoomLog $roomLog): void
    {
        $this->getEntityManager()->persist($roomLog);
        $this->getEntityManager()->flush();
    }

    public function saveAll(array $roomLogs): void
    {
        $entityManager = $this->getEntityManager();

        try {
            $entityManager->beginTransaction();
            foreach ($roomLogs as $roomLog) {
                $entityManager->persist($roomLog);
            }

            $entityManager->flush();
            $entityManager->commit();
        } catch (\Throwable $e) {
            $entityManager->rollback();
            $entityManager->close();

            throw $e;
        }
    }

    public function startTransaction(): void
    {
        $this->getEntityManager()->beginTransaction();
    }

    public function commitTransaction(): void
    {
        $this->getEntityManager()->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->getEntityManager()->rollback();
        $this->getEntityManager()->close();
    }

    public function getOneBy(array $parameters): ?RoomLog
    {
        $log = $this->findOneBy($parameters);

        return $log instanceof RoomLog ? $log : null;
    }

    public function getBy(array $parameters): array
    {
        return $this->findBy($parameters);
    }
}
