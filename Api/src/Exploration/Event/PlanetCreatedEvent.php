<?php

declare(strict_types=1);

namespace Mush\Exploration\Event;

use Mush\Exploration\Entity\Planet;
use Mush\Game\Event\AbstractGameEvent;

final class PlanetCreatedEvent extends AbstractGameEvent
{
    public function __construct(
        private Planet $planet,
        array $tags = [],
        \DateTime $time = new \DateTime()
    ) {
        parent::__construct($tags, $time);
    }

    public function getAuthorUserId(): int
    {
        return $this->planet->getPlayer()->getUser()->getId();
    }

    public function getLanguage(): string
    {
        return $this->planet->getPlayer()->getLanguage();
    }
}
