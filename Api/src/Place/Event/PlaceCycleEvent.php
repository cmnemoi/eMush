<?php

namespace Mush\Place\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Place\Entity\Place;

class PlaceCycleEvent extends AbstractGameEvent
{
    public const PLACE_NEW_CYCLE = 'place.new.cycle';
    public const PLACE_NEW_DAY = 'place.new.day';

    protected Place $place;

    public function __construct(
        Place $place,
        array $tags,
        \DateTime $time
    ) {
        parent::__construct($tags, $time);

        $this->place = $place;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    public function getModifiers(): ModifierCollection
    {
        $player = $this->player;

        if ($player === null) {
            return $this->getPlace()->getAllModifiers()->getEventModifiers($this);
        }

        return $player->getAllModifiers()->getEventModifiers($this);
    }
}
