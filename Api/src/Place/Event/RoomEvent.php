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

    private Place $room;
    private ?string $reason = null;
    private bool $isGravity = true;
    private \DateTime $time;

    public function __construct(Place $room, $time)
    {
        $this->time = $time;
        $this->room = $room;
    }
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

    public function setReason(?string $reason): RoomEvent
    {
        $this->reason = $reason;

        return $this;
    }

    public function isGravity(): bool{
        return $this->isGravity();
    }

    public function setIsGravity(bool $isGravity): RoomEvent
    {
        $this->isGravity = $isGravity;
        return  $this;
    }
}
