<?php

namespace Mush\Room\Event;

use Mush\Room\Entity\Room;
use Symfony\Contracts\EventDispatcher\Event;

class RoomEvent extends Event
{
    public const TREMOR = 'tremor';
    public const ELECTRIC_ARC = 'electric.arc';
    public const STARTING_FIRE = 'starting.fire';

    private Room $room;
    private ?string $reason = null;
    private \DateTime $time;

    public function __construct(Room $room, $time = null)
    {
        $this->time = $time ?? new \DateTime();
        $this->room = $room;
    }

    public function getRoom(): Room
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
}
