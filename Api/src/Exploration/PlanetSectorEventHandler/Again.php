<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Player\Entity\Player;

final class Again extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::AGAIN;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $planetSector = $event->getPlanetSector();
        $planetSector->unvisit();

        $this->entityManager->persist($planetSector);

        $logParameters = $this->getLogParameters($event);

        $lostPlayers = $event->getExploration()->getDaedalus()->getLostPlayers();
        if (!$lostPlayers->isEmpty()) {
            /** @var Player $lostPlayer */
            $lostPlayer = $lostPlayers->first();
            $logParameters[$lostPlayer->getLogKey()] = $lostPlayer->getLogName();
        }

        return $this->createExplorationLog($event, $logParameters);
    }
}
