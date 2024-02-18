<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;

final class Harvest extends AbstractLootItemsEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::HARVEST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $fruits = $this->createRandomItemsFromEvent($event);

        $logParameters = [
            'quantity' => $fruits->count(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
