<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractCycleEvent;
use Mush\Place\Entity\Place;

class PlaceCycleEvent extends AbstractCycleEvent
{
    public const PLACE_NEW_CYCLE = 'place.new.cycle';
    public const PLACE_NEW_DAY = 'place.new.day';

    private Place $place;

    public function __construct(Place $place, \DateTime $time)
    {
        parent::__construct($time);

        $this->place = $place;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
