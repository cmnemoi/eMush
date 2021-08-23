<?php

namespace Mush\Place\Event;

use Mush\Place\Entity\Place;
use Symfony\Contracts\EventDispatcher\Event;

class RoomEvent extends Event
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

    public function getRoom(): Place
    {
        return $this->room;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }

    public function getReason(): ?string
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
