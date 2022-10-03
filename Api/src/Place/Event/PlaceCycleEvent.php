<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Place\Entity\Place;

class PlaceCycleEvent extends AbstractModifierHolderEvent
{
    public const PLACE_NEW_CYCLE = 'place.new.cycle';
    public const PLACE_NEW_DAY = 'place.new.day';

    protected Place $place;

    public function __construct(
        Place $place,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($place, $reason, $time);

        $this->place = $place;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
