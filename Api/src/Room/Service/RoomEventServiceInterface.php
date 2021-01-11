<?php

namespace Mush\Room\Service;

use Mush\Room\Entity\Room;

interface RoomEventServiceInterface
{
    public function handleIncident(Room $room, \DateTime $date): Room;
}
