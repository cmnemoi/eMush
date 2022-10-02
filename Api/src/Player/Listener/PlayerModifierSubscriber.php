<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private PlayerVariableServiceInterface $playerVariableService;
    private EventServiceInterface $eventService;

    public function __construct(
        PlayerVariableServiceInterface $playerVariableService,
        EventServiceInterface $eventService
    ) {
        $this->playerVariableService = $playerVariableService;
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        switch ($playerEvent->getModifiedVariable()) {
            case PlayerVariableEnum::MORAL_POINT:
                $this->handleMoralPointModifier($playerEvent);

                return;

            case PlayerVariableEnum::HEALTH_POINT:
                $this->handleHealthPointModifier($playerEvent);

                return;

            case PlayerVariableEnum::MOVEMENT_POINT:
                $this->handleMovementPointModifier($playerEvent);

                return;

            case PlayerVariableEnum::ACTION_POINT:
                $this->handleActionPointModifier($playerEvent);

                return;

            case PlayerVariableEnum::SATIETY:
                $this->handleSatietyPointModifier($playerEvent);

                return;
        }
    }

    private function handleActionPointModifier(PlayerVariableEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleActionPointModifier($delta, $player);
    }

    private function handleMovementPointModifier(PlayerVariableEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMovementPointModifier($delta, $player);
    }

    private function handleHealthPointModifier(PlayerVariableEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleHealthPointModifier($delta, $player);

        $reason = $playerEvent->getReasons()[0];
        $deathCause = EndCauseEnum::DEATH_CAUSE_MAP;

        if ($player->getHealthPoint() <= 0) {
            $deathReason = EndCauseEnum::INJURY;

            if (isset($deathCause[$reason])) {
                $deathReason = $deathCause[$reason];
            }

            // To be more clear of what's happening
            $deathEvent = new PlayerEvent(
                $playerEvent->getPlayer(),
                $deathReason,
                $playerEvent->getTime()
            );

            $this->eventService->callEvent($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function handleMoralPointModifier(PlayerVariableEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMoralPointModifier($delta, $player);
    }

    private function handleSatietyPointModifier(PlayerVariableEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleSatietyModifier($delta, $player);
    }
}
