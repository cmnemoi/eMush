<?php

namespace Mush\Room\Event;

use Mush\Game\Event\AbstractCycleEvent;
use Mush\Room\Entity\Room;

class RoomCycleEvent extends AbstractCycleEvent
{
    public const ROOM_NEW_CYCLE = 'room.new.cycle';
    public const ROOM_NEW_DAY = 'room.new.day';

    private Room $room;

    public function __construct(Room $room, \DateTime $time)
    {
        parent::__construct($time);

        $this->room = $room;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }
}
