<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;

final class Again extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::AGAIN;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $planetSector = $event->getPlanetSector();
        $planetSector->unvisit();

        $this->entityManager->persist($planetSector);

        return $this->createExplorationLog($event);
    }
}
