<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Equipment\Enum\GearItemEnum;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

abstract class AbstractRemoveHealthToARandomExplorator extends AbstractPlanetSectorEventHandler
{
    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();

        // also remove health to explorators stucked in the ship for landing events
        $explorators = $event->getPlanetSector()->getName() === PlanetSectorEnum::LANDING ?
            $exploration->getAliveExplorators() :
            $exploration->getNotLostActiveExplorators();

        $exploratorToInjure = $this->randomService->getRandomPlayer($explorators);
        $healthLost = $this->drawEventOutputQuantity($event->getOutputTable());

        $playerVariableEvent = new PlayerVariableEvent(
            player: $exploratorToInjure,
            variableName: PlayerVariableEnum::HEALTH_POINT,
            quantity: -$healthLost,
            tags: $event->getTags(),
            time: new \DateTime()
        );
        $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);

        $logParameters = [
            $exploratorToInjure->getLogKey() => $exploratorToInjure->getLogName(),
            'quantity' => $healthLost,
            'has_rope' => $exploratorToInjure->hasEquipmentByName(GearItemEnum::ROPE) ? 'true' : 'false',
        ];

        return $this->createExplorationLog($event, $logParameters);
    }
}
