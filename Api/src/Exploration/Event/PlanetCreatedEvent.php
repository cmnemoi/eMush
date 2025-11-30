<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Exploration\Entity\Planet;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

final class PlanetCreatedEvent extends AbstractGameEvent
{
    public function __construct(
        private Planet $planet,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function getAuthor(): Player
    {
        return $this->planet->getPlayer();
    }

    public function getLanguage(): string
    {
        return $this->planet->getPlayer()->getLanguage();
    }
}
