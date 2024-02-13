<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Exploration\PlanetSectorEventHandler\AbstractPlanetSectorEventHandler;

final class PlanetSectorEventHandlerService implements PlanetSectorEventHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractPlanetSectorEventHandler $planetSectorEventHandler): void
    {
        $this->strategies[$planetSectorEventHandler->getName()] = $planetSectorEventHandler;
    }

    public function getPlanetSectorEventHandler(string $name): ?AbstractPlanetSectorEventHandler
    {
        if (!isset($this->strategies[$name])) {
            return null;
        }

        return $this->strategies[$name];
    }
}
