<?php

declare(strict_types=1);

namespace Mush\Equipment\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;

final class NPCMovedEvent extends AbstractNPCEvent
{
    public function __construct(
        GameEquipment $NPC,
        private Place $oldRoom,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($NPC, $tags, $time);
    }

    public function getOldRoom(): Place
    {
        return $this->oldRoom;
    }
}
