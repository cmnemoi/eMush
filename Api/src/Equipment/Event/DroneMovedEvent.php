<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\Drone;
use Mush\Place\Entity\Place;

final class DroneMovedEvent extends AbstractDroneEvent
{
    public function __construct(
        Drone $drone,
        private Place $oldRoom,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($drone, $tags, $time);
    }

    public function getOldRoom(): Place
    {
        return $this->oldRoom;
    }
}
