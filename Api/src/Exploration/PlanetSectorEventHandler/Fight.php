<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

final class Fight extends AbstractPlanetSectorEventHandler
{
    public function getName(): string
    {
        return PlanetSectorEvent::FIGHT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $creatureStrengthTable = $event->getOutputQuantityTable();
        if (!$creatureStrengthTable) {
            throw new \RuntimeException('Fight event must have an output quantity table');
        }

        $creatureStrength = (int) $this->randomService->getSingleRandomElementFromProbaCollection($creatureStrengthTable);
        $expeditionStrength = $this->getExpeditionStrength($event);
        $damage = max(0, $creatureStrength - $expeditionStrength);

        $logParameters = [
            'creature_strength' => $creatureStrength,
            'expedition_strength' => $expeditionStrength,
            'damage' => $damage,
        ];

        if ($damage === 0) {
            return $this->createExplorationLog($event, $logParameters);
        }

        for ($i = 0; $i < $creatureStrength; ++$i) {
            $explorator = $this->randomService->getRandomPlayer($event->getExploration()->getNotLostExplorators());
            $playerEvent = new PlayerVariableEvent(
                player: $explorator,
                variableName: PlayerVariableEnum::HEALTH_POINT,
                quantity: -1,
                tags: $event->getTags(),
                time: $event->getTime()
            );
            $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        }

        return $this->createExplorationLog($event, $logParameters);
    }

    private function getExpeditionStrength(PlanetSectorEvent $event): int
    {
        $Strength = $event->getExploration()->getExplorators()->count();

        return $Strength;
    }
}
