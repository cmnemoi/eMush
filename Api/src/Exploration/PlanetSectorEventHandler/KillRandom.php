<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Event\PlayerEvent;

final class KillRandom extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::KILL_RANDOM;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $activeExplorators = $event->getExploration()->getActiveExplorators();
        $playerToKill = $this->randomService->getRandomPlayer($activeExplorators);

        $deathEvent = new PlayerEvent(
            player: $playerToKill,
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        return $this->createExplorationLog($event);
    }
}
