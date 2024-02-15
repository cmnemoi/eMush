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
        $food = $this->createRandomItemsFromEvent($event);
        $finder = $this->randomService->getRandomElement($event->getExploration()->getNotLostExplorators()->toArray());

        $logParameters = [
            'quantity' => $food->count(),
            $food->first()->getLogKey() => $food->first()->getLogName(),
            $finder->getLogKey() => $finder->getLogName(),
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
