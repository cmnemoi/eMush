<?php

namespace Mush\Room\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;

interface RoomServiceInterface
{
    public function persist(Room $room): Room;

    public function findById(int $id): ?Room;

    public function createRoom(RoomConfig $roomConfig, Daedalus $daedalus): Room;

    public function handleCycleIncident(Room $room, \DateTime $date): Room;
}
