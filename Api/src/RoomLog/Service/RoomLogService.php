<?php

namespace Mush\RoomLog\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\RoomLogParameter;
use Mush\RoomLog\Repository\RoomLogRepository;

class RoomLogService implements RoomLogServiceInterface
{
    private EntityManagerInterface $entityManager;

    private RoomLogRepository $repository;

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

    public function createLog(
        string $logKey,
        Player $player,
        Room $room,
        string $visibility,
        RoomLogParameter $roomLogParameter,
        \DateTime $date = null
    ): RoomLog {
        $roomLog= new RoomLog();
        $roomLog
            ->setLog($logKey)
            ->setPlayer($player)
            ->setRoom($room)
            ->setVisibility($visibility)
            ->setDate($date ?? new \DateTime('now'))
            ->setParams($roomLogParameter->toArray())
        ;

        return $this->persist($roomLog);
    }
}
