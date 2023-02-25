<?php

namespace Mush\Player\Listener;

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
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();
        $variableName = $playerEvent->getVariableName();

        $this->playerVariableService->handleGameVariableChange($variableName, $delta, $player);

        switch ($playerEvent->getVariableName()) {
            case PlayerVariableEnum::HEALTH_POINT:
                $this->handleHealthPointModifier($player, $playerEvent, $playerEvent->getTime());

                return;

            case PlayerVariableEnum::SPORE:
                $this->handleInfection($player, $playerEvent);

                return;
        }
    }

    public function onChangeMaxValue(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();
        $variable = $playerEvent->getVariable();

        $variable->changeMaxValue($delta);

        $this->playerService->persist($player);
    }

    private function handleHealthPointModifier(Player $player, PlayerVariableEvent $event, \DateTime $time): void
    {
        if ($player->getHealthPoint() <= 0) {
            $deathReason = $event->mapLog(EndCauseEnum::DEATH_CAUSE_MAP);

            if ($deathReason === null) {
                $event->addTag(EndCauseEnum::INJURY);
            }

            $this->eventService->callEvent($event, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function handleInfection(Player $player, PlayerEvent $playerEvent): void
    {
        if ($player->getVariableByName(PlayerVariableEnum::SPORE)->isMax()) {
            $this->eventService->callEvent($playerEvent, PlayerEvent::CONVERSION_PLAYER);
        }

        $this->eventService->callEvent($playerEvent, PlayerEvent::INFECTION_PLAYER);
    }
}
