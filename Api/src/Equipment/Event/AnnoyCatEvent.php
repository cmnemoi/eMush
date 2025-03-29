<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

final class AnnoyCatEvent extends AbstractNPCEvent
{
    public function __construct(
        GameEquipment $NPC,
        private Place $place,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($NPC, $tags, $time);
    }

    public function getPlace(): Place
    {
        return $this->place;
    }
}
