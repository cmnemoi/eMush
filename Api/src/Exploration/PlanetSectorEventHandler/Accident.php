<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Event\PlanetSectorEvent;

final class Accident extends AbstractRemoveHealthToARandomExplorator
{
    public function getName(): string
    {
        return PlanetSectorEvent::ACCIDENT;
    }
}
