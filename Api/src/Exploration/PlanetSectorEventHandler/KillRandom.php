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
        $playerToKill = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostActiveExplorators());

        $deathEvent = new PlayerEvent(
            player: $playerToKill,
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);

        $logParameters = $this->getLogParameters($event);
        $logParameters[$playerToKill->getLogKey()] = $playerToKill->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }
}
