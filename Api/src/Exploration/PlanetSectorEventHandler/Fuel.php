<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Event\PlanetSectorEvent;

final class Fuel extends AbstractCreateLootStatus
{
    public function getName(): string
    {
        return PlanetSectorEvent::FUEL;
    }
}
