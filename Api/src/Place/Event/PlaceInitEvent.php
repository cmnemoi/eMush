<?php

namespace Mush\Place\Event;

use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;

class PlaceInitEvent extends PlaceCycleEvent
{
    public const NEW_PLACE = 'new.place';

    private PlaceConfig $placeConfig;

    public function __construct(
        Place $place,
        PlaceConfig $placeConfig,
        string $reason,
        \DateTime $time
    ) {
        parent::__construct($place, $reason, $time);

        $this->placeConfig = $placeConfig;
    }

    public function getPlaceConfig(): PlaceConfig
    {
        return $this->placeConfig;
    }
}
