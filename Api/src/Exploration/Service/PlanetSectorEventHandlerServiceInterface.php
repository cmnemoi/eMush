<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\PlanetSectorEventHandler\AbstractPlanetSectorEventHandler;

interface PlanetSectorEventHandlerServiceInterface
{
    public function getPlanetSectorEventHandler(string $name): ?AbstractPlanetSectorEventHandler;
}
