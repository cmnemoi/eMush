<?php

namespace Mush\RoomLog\Service;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\ActionParameter;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;

interface RoomLogServiceInterface
{
    public function createLog(
        string $logKey,
        Place $place,
        string $visibility,
        string $type,
        ?Player $player = null,
        array $parameters = [],
        \DateTime $dateTime = null
    ): RoomLog;

    public function createLogFromActionResult(
        string $actionName,
        ActionResult $actionResult,
        Player $player,
        ?ActionParameter $actionParameter,
    ): ?RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;

    public function getRoomLog(Player $player): array;
}
