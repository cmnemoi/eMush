<?php

namespace Mush\Room\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Room\Entity\Room;
use Mush\Room\Entity\RoomConfig;

interface RoomIncidentServiceInterface
{
    public function handleIncident(Room $room): Room;

    public function handleElectricArc(Room $room): Room;

    public function handleTremor(Room $room): Room;

    public function handleFire(Room $room): Room;
}
