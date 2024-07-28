<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;

final class KillRandom extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::KILL_RANDOM;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $playerToKill = $this->drawPlayerToKill($event);

        $this->killPlayer($playerToKill, $event);

        $logParameters = $this->getLogParameters($event);
        $logParameters[$playerToKill->getLogKey()] = $playerToKill->getLogName();

        return $this->createExplorationLog($event, $logParameters);
    }

    private function drawPlayerToKill(PlanetSectorEvent $event): Player
    {
        $exploration = $event->getExploration();
        $nonSurvivalists = $exploration->getActiveNonSurvivalistExplorators();
        if ($nonSurvivalists->count() > 0) {
            return $this->randomService->getRandomPlayer($nonSurvivalists);
        }

        return $this->randomService->getRandomPlayer($exploration->getNotLostActiveExplorators());
    }

    private function killPlayer(Player $player, PlanetSectorEvent $event): void
    {
        $deathEvent = new PlayerEvent(
            player: $player,
            tags: $event->getTags(),
            time: $event->getTime(),
        );
        $this->eventService->callEvent($deathEvent, PlayerEvent::DEATH_PLAYER);
    }
}
