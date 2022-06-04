<?php

namespace Mush\Player\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private PlayerVariableServiceInterface $playerVariableService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PlayerVariableServiceInterface $playerVariableService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->playerVariableService = $playerVariableService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => 'onChangeVariable',
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerModifierEvent) {
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

    private function handleActionPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleActionPointModifier($delta, $player);
    }

    private function handleMovementPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMovementPointModifier($delta, $player);
    }

    private function handleHealthPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleHealthPointModifier($delta, $player);

        if ($player->getHealthPoint() === 0) {
            $playerEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    private function handleMoralPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMoralPointModifier($delta, $player);
    }

    private function handleSatietyPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleSatietyModifier($delta, $player);
    }
}
