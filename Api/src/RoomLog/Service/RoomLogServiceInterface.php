<?php

namespace Mush\RoomLog\Service;

use Mush\Item\Entity\GameItem;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogServiceInterface
{
    public function createPlayerLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createItemLog(
        string $logKey,
        Room $room,
        Player $player,
        GameItem $item,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createRoomLog(
        string $logKey,
        Room $room,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;

    public function getRoomLog(Player $player): array;
}
