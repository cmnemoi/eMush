<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;

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
        $dispatchedEvents = $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
        $ropeWorked = $dispatchedEvents->filter(static fn (AbstractGameEvent $event) => $event->hasTag(ModifierNameEnum::ROPE_MODIFIER))->count() > 0;
        $survivalistWorked = $dispatchedEvents->filter(static fn (AbstractGameEvent $event) => $event->hasTag(ModifierNameEnum::PLAYER_PLUS_1_HEALTH_POINT_ON_CHANGE_VARIABLE_IF_FROM_PLANET_SECTOR_EVENT))->count() > 0;

        $logParameters = $this->getLogParameters($event);
        $logParameters['quantity'] = $healthLost;
        $logParameters[$exploratorToInjure->getLogKey()] = $exploratorToInjure->getLogName();
        $logParameters['rope_worked'] = $ropeWorked ? 'true' : 'false';
        $logParameters['skill_reduced_damage_for_player'] = $survivalistWorked ? \sprintf(
            '////%s',
            $this->getSkillReducedDamageForPlayer($exploratorToInjure, SkillEnum::SURVIVALIST)
        ) : '';

        return $this->createExplorationLog($event, $logParameters);
    }
}
