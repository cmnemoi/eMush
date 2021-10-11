<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractLoggedEvent;
use Mush\Place\Entity\Place;
use Mush\RoomLog\Enum\VisibilityEnum;

class RoomEvent extends PlaceCycleEvent implements AbstractLoggedEvent
{
    public const TREMOR = 'tremor';
    public const ELECTRIC_ARC = 'electric.arc';
    public const STARTING_FIRE = 'starting.fire';
    public const STOP_FIRE = 'stop.fire';

    private string $visibility = VisibilityEnum::PUBLIC;

    public function getRoom(): Place
    {
        return $this->place;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
