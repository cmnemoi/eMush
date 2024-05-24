<?php

declare(strict_types=1);

namespace Mush\Player\Event;

use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

final class PlayerChangedPlaceEvent extends PlayerEvent
{
    public function __construct(
        protected Player $player,
        private Place $oldPlace,
        protected array $tags = [],
        protected \DateTime $time = new \DateTime()
    ) {
        parent::__construct($player, $tags, $time);

        $this->oldPlace = $oldPlace;
    }

    public function getOldPlace(): Place
    {
        return $this->oldPlace;
    }
}
