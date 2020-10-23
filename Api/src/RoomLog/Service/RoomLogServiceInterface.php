<?php

namespace Mush\RoomLog\Service;

use Mush\Item\Entity\Item;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\RoomLogParameter;

interface RoomLogServiceInterface
{
    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog;

    public function createItemLog(
        string $logKey,
        Room $room,
        Item $player,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog;

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime,
        ?RoomLogParameter $roomLogParameter = null
    ): RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;
}
