<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

final class MushTrap extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::MUSH_TRAP;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $infectionRate = (int) $this->randomService->getSingleRandomElementFromProbaCollection($event->getOutputTable());

        foreach ($event->getExploration()->getNotLostExplorators() as $explorator) {
            if ($this->randomService->isSuccessful($infectionRate)) {
                $playerVariableEvent = new PlayerVariableEvent(
                    $explorator,
                    PlayerVariableEnum::SPORE,
                    1,
                    $event->getTags(),
                    $event->getTime(),
                );
                $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
            }
        }

        return $this->createExplorationLog($event);
    }
}
