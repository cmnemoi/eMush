<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;

abstract class AbstractRemoveHealthToAllExplorators extends AbstractPlanetSectorEventHandler
{
    /**
     * @psalm-suppress InvalidArgument
     */
    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $exploration = $event->getExploration();

        // also remove health to explorators stucked in the ship for landing events
        $explorators = $event->getPlanetSector()->getName() === PlanetSectorEnum::LANDING ?
            $exploration->getAliveExplorators() :
            $exploration->getNotLostActiveExplorators();

        $healthLost = $this->drawEventOutputQuantity($event->getOutputTable());

        $dispatchedEvents = [];
        foreach ($explorators as $explorator) {
            $playerVariableEvent = new PlayerVariableEvent(
                player: $explorator,
                variableName: PlayerVariableEnum::HEALTH_POINT,
                quantity: -$healthLost,
                tags: $event->getTags(),
                time: new \DateTime()
            );
            $dispatchedEvents = array_merge(
                $dispatchedEvents,
                $this->eventService->callEvent($playerVariableEvent, VariableEventInterface::CHANGE_VARIABLE)->toArray()
            );
        }

        $survivalistReducedDamageEvents = array_filter($dispatchedEvents, static fn ($event) => $event->hasTag(ModifierNameEnum::PLAYER_PLUS_1_HEALTH_POINT_ON_CHANGE_VARIABLE_IF_FROM_PLANET_SECTOR_EVENT));

        $logParameters = $this->getLogParameters($event);
        $logParameters['quantity'] = $healthLost;
        $logParameters['skill_reduced_damage_for_player'] = $survivalistReducedDamageEvents ? sprintf(
            '//%s',
            $explorators
                ->filter(static fn (Player $explorator) => $explorator->hasSkill(SkillEnum::SURVIVALIST))
                ->map(fn (Player $explorator) => $this->getSkillReducedDamageForPlayer($explorator, SkillEnum::SURVIVALIST))
                ->reduce(static fn ($carry, $item) => sprintf('%s//%s', $carry, $item))
        ) : '';

        return $this->createExplorationLog($event, $logParameters);
    }
}
