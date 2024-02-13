<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

abstract class AbstractRemoveHealthToAllExplorators extends AbstractPlanetSectorEventHandler
{
    public function handle(PlanetSectorEvent $event): void
    {
        $exploration = $event->getExploration();

        // also remove health to explorators stucked in the ship for landing events
        $explorators = $event->getPlanetSector()->getName() === PlanetSectorEnum::LANDING ?
            $exploration->getExplorators() :
            $exploration->getActiveExplorators();

        $healthLost = $this->drawEventOutputQuantity($event->getOutputQuantityTable());
        foreach ($explorators as $explorator) {
            $playerVariableEvent = new PlayerVariableEvent(
                player: $explorator,
                variableName: PlayerVariableEnum::HEALTH_POINT,
                quantity: -$healthLost,
                tags: $event->getTags(),
                time: new \DateTime()
            );
            $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        $logParameters = array_merge(['quantity' => $healthLost], $event->getLogParameters());

        $this->createExplorationLog($event, $logParameters);
    }
}
