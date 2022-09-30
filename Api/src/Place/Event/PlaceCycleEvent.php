<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Place\Entity\Place;

class PlaceCycleEvent extends AbstractGameEvent
{
    public const PLACE_NEW_CYCLE = 'place.new.cycle';
    public const PLACE_NEW_DAY = 'place.new.day';

    protected Place $place;

    public function __construct(
        Place $place,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($reason, $place, $time);

        $this->place = $place;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
