<?php

namespace Mush\RoomLog\Service;

use Mush\Equipment\Entity\GameEquipment;
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

    public function createQuantityLog(
        string $logKey,
        Room $room,
        Player $player,
        string $visibility,
        int $quantity,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createEquipmentLog(
        string $logKey,
        Room $room,
        ?Player $player,
        GameEquipment $gameEquipment,
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
