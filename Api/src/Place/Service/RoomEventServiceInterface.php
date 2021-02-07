<?php

namespace Mush\Place\Service;

use Mush\Place\Entity\Place;

interface RoomEventServiceInterface
{
    public function handleIncident(Place $room, \DateTime $date): Place;

    public function handleNewFire(Place $room, \DateTime $date): Place;
}
