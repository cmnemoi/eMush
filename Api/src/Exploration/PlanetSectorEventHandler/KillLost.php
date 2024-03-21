<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Event\PlayerEvent;

final class KillLost extends AbstractPlanetSectorEventHandler
{
    private const NUMBER_OF_DESCRIPTIONS = 2;

    public function getName(): string
    {
        return PlanetSectorEvent::KILL_LOST;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $playerToKill = $this->randomService->getRandomPlayer($event->getExploration()->getDaedalus()->getLostPlayers());

        $playerEvent = new PlayerEvent(
            $playerToKill,
            $event->getTags(),
            $event->getTime()
        );
        $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);

        $logParameters = $this->getLogParameters($event);
        $logParameters[$playerToKill->getLogKey()] = $playerToKill->getLogName();
        $logParameters['version'] = $this->randomService->random(1, self::NUMBER_OF_DESCRIPTIONS);

        return $this->createExplorationLog($event, $logParameters);
    }
}
