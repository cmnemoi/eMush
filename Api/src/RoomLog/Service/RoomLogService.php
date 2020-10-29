<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\RoomLogParameter;
use Mush\RoomLog\Repository\RoomLogRepository;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RoomLogRepository $repository;

    public function __construct(EntityManagerInterface $entityManager, RoomLogRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function persist(RoomLog $roomLog): RoomLog
    {
        $this->entityManager->persist($roomLog);
        $this->entityManager->flush();

        return $roomLog;
    }

    public function findById(int $id): ?RoomLog
    {
        return $this->repository->find($id);
    }

    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog {
        $roomLog= new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setPlayer($player)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($date ?? new \DateTime('now'))
            ->setParams($roomLogParameter ? $roomLogParameter->toArray() : [])
        ;

        return $this->persist($roomLog);
    }

    public function createItemLog(
        string $logKey,
        Room $room,
        GameItem $item,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog {
        $roomLog= new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setItem($item)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($date ?? new \DateTime('now'))
            ->setParams($roomLogParameter ? $roomLogParameter->toArray() : [])
        ;

        return $this->persist($roomLog);
    }

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog {
        $roomLog= new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($date ?? new \DateTime('now'))
            ->setParams($roomLogParameter ? $roomLogParameter->toArray() : [])
        ;

        return $this->persist($roomLog);
    }
}
