<?php

namespace Mush\Player\Listener;

use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
        EventServiceInterface $eventService
    ) {
        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
            VariableEventInterface::CHANGE_VALUE_MAX => 'onChangeMaxValue',
            VariableEventInterface::SET_VALUE => 'onSetValue',
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $delta = $playerEvent->getRoundedQuantity();

        $this->handlePlayerVariableChange($playerEvent, $delta);
    }

    public function onSetValue(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $newValue = $playerEvent->getRoundedQuantity();
        $variable = $playerEvent->getVariable();

        $delta = $newValue - $variable->getValue();

        $this->handlePlayerVariableChange($playerEvent, $delta);
    }

    public function onChangeMaxValue(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getRoundedQuantity();
        $variable = $playerEvent->getVariable();

        $variable->changeMaxValue($delta);

        $this->playerService->persist($player);
    }

    private function handlePlayerVariableChange(PlayerVariableEvent $playerEvent, int $delta): void
    {
        $variableName = $playerEvent->getVariableName();
        $player = $playerEvent->getPlayer();

        if ($variableName === PlayerVariableEnum::SPORE && $delta > 0) {
            $initialMushStatus = $player->isMush();
            for ($i = 0; $i < $delta; ++$i) {
                if ($this->playerHasChangedSide($player, $initialMushStatus)) {
                    break;
                }
                $player = $this->playerVariableService->handleGameVariableChange($variableName, 1, $player);
                $this->handleInfection($player, $playerEvent);
            }
        } else {
            $player = $this->playerVariableService->handleGameVariableChange($variableName, $delta, $player);
            if ($variableName === PlayerVariableEnum::HEALTH_POINT) {
                $this->handleHealthPointModifier($player, $playerEvent);
            }
        }
    }

    private function handleHealthPointModifier(Player $player, PlayerVariableEvent $event): void
    {
        if ($player->getHealthPoint() <= 0) {
            $this->playerService->killPlayer(
                player: $player,
                endReason: $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP) ?? EndCauseEnum::INJURY,
                time: $event->getTime(),
                author: $event->getAuthor()
            );
        }
    }

    private function handleInfection(Player $player, PlayerVariableEvent $playerEvent): void
    {
        if ($player->isMush() && !$playerEvent->hasTag(PlanetSectorEvent::MUSH_TRAP)) {
            return;
        }

        if ($player->getVariableByName(PlayerVariableEnum::SPORE)->isMax() && !$player->isMush()) {
            $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        } elseif ($playerEvent->getRoundedQuantity() > 0) {
            $this->eventService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);
        }
    }

    private function playerHasChangedSide(Player $player, bool $wasMush): bool
    {
        return $player->isMush() !== $wasMush;
    }
}
