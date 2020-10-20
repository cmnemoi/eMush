<?php

namespace Mush\RoomLog\Service;

use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Entity\RoomLogParameter;

interface RoomLogServiceInterface
{
    public function createLog(string $logKey, Player $player, Room $room, string $visibility, RoomLogParameter $roomLogParameter): RoomLog;

    public function persist(RoomLog $roomLog): RoomLog;

    public function findById(int $id): ?RoomLog;
}