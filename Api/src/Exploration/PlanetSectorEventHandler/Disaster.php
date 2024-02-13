<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Event\PlanetSectorEvent;

final class Disaster extends AbstractRemoveHealthToAllExplorators
{
    public function getName(): string
    {
        return PlanetSectorEvent::DISASTER;
    }
}
