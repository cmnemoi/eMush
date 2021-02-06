<?php

namespace Mush\RoomLog\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\Target;

interface RoomLogServiceInterface
{
    public function createActionLog(
        string $logKey,
        Place $place,
        Player $player,
        ?Target $target,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionResult(string $actionName, ActionResult $actionResult, Player $player): ?RoomLog;

    public function createPlayerLog(
        string $logKey,
        Place $place,
        Player $player,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createQuantityLog(
        string $logKey,
        Place $place,
        Player $player,
        string $visibility,
        int $quantity,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createEquipmentLog(
        string $logKey,
        Place $place,
        ?Player $player,
        GameEquipment $gameEquipment,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function createRoomLog(
        string $logKey,
        Place $place,
        string $visibility,
        \DateTime $dateTime = null
    ): RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;

    public function getRoomLog(Player $player): array;
}
