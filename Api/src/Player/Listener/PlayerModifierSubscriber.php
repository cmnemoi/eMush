<?php

namespace Mush\Player\Listener;

use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Event\PlayerEventInterface;
use Mush\Player\Event\PlayerModifierEventInterface;
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
            PlayerModifierEventInterface::ACTION_POINT_MODIFIER => 'onActionPointModifier',
            PlayerModifierEventInterface::MOVEMENT_POINT_MODIFIER => 'onMovementPointModifier',
            PlayerModifierEventInterface::HEALTH_POINT_MODIFIER => 'onHealthPointModifier',
            PlayerModifierEventInterface::MORAL_POINT_MODIFIER => 'onMoralPointModifier',
            PlayerModifierEventInterface::SATIETY_POINT_MODIFIER => 'onSatietyPointModifier',
            PlayerModifierEventInterface::MOVEMENT_POINT_CONVERSION => 'onMovementPointConversion',
        ];
    }

    public function onActionPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleActionPointModifier($delta, $player);
    }

    public function onMovementPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMovementPointModifier($delta, $player);
    }

    public function onHealthPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleHealthPointModifier($delta, $player);

        if ($player->getHealthPoint() === 0) {
            $playerEvent->setVisibility(VisibilityEnum::PUBLIC);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEventInterface::DEATH_PLAYER);
        }
    }

    public function onMoralPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleMoralPointModifier($delta, $player);

        if ($player->getMoralPoint() === 0) {
            $depressionEvent = new PlayerEventInterface(
                $player,
                EndCauseEnum::DEPRESSION,
                $playerEvent->getTime()
            );

            $this->eventDispatcher->dispatch($depressionEvent, PlayerEventInterface::DEATH_PLAYER);
        }
    }

    public function onSatietyPointModifier(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        $this->playerVariableService->handleSatietyModifier($delta, $player);
    }

    public function onMovementPointConversion(PlayerModifierEventInterface $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getQuantity();

        if ($player->getActionPoint() < 1) {
            throw new \Exception('Trying to convert movement point without action point');
        }

        $this->playerVariableService->handleActionPointModifier(-1, $player);
        $this->playerVariableService->handleMovementPointModifier($delta, $player);
    }
}
