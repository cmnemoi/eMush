<?php

namespace Mush\RoomLog\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\Target;

interface RoomLogServiceInterface
{
    public function createActionLog(
        string $logKey,
        Room $room,
        Player $player,
        ?Target $target,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionResult(string $actionName, ActionResult $actionResult, Player $player): ?RoomLog;

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
