<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Event\PlayerEvent;

final class KillAll extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::KILL_ALL;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        foreach ($event->getExploration()->getNotLostActiveExplorators() as $player) {
            $deathEvent = new PlayerEvent(
                player: $player,
                tags: $event->getTags(),
                time: $event->getTime(),
            );
            $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
        }

        return $this->createExplorationLog($event, $this->getLogParameters($event));
    }
}
