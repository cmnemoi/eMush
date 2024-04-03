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

    /**
     * @psalm-suppress PossiblyFalseReference
     */
    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $food = $this->createRandomItemsFromEvent($event);
        $finder = $this->randomService->getRandomElement($event->getExploration()->getNotLostActiveExplorators()->toArray());

        $logParameters = $this->getLogParameters($event);

        $logParameters['quantity'] = $food->count();
        $logParameters[$food->first()->getLogKey()] = $food->first()->getLogName();
        $logParameters[$finder->getLogKey()] = $finder->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }
}
