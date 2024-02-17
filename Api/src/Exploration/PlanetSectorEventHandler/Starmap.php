<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;

final class Starmap extends AbstractLootItemsEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::STARMAP;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $this->createRandomItemsFromEvent($event);

        return $this->createExplorationLog($event);
    }
}
