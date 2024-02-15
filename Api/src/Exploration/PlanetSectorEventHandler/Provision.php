<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;

final class Provision extends AbstractLootItemsEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::PROVISION;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $food = $this->createRandomGameItemsFromEvent($event);

        $logParameters = [
            'quantity' => $food->count(),
            $food->first()->getLogKey() => $food->first()->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
