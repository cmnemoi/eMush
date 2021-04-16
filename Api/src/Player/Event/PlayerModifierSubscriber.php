<?php

namespace Mush\Player\Event;

use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private PlayerVariableServiceInterface $playerVariableService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        PlayerVariableServiceInterface $playerVariableService,
        EventDispatcherInterface $eventDispatcher,
    ) {
        $this->playerVariableService = $playerVariableService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerModifierEvent::ACTION_POINT_MODIFIER => 'onActionPointModifier',
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER => 'onMovementPointModifier',
            PlayerModifierEvent::HEALTH_POINT_MODIFIER => 'onHealthPointModifier',
            PlayerModifierEvent::MORAL_POINT_MODIFIER => 'onMoralPointModifier',
            PlayerModifierEvent::SATIETY_POINT_MODIFIER => 'onSatietyPointModifier',
        ];
    }

    public function onActionPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        $this->playerVariableService->handleActionPointModifier($delta, $player);
    }

    public function onMovementPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        $this->playerVariableService->handleMovementPointModifier($delta, $player);
    }

    public function onHealthPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        $this->playerVariableService->handleHealthPointModifier($delta, $player);

        if ($player->getHealthPoint() === 0) {
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    public function onMoralPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        $this->playerVariableService->handleMoralPointModifier($delta, $player);

        if ($player->getMoralPoint() === 0) {
            $playerEvent->setReason(EndCauseEnum::DEPRESSION);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }

    public function onSatietyPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        $this->playerVariableService->handleSatietyModifier($delta, $player);

        if ($player->getMoralPoint() === 0) {
            $playerEvent->setReason(EndCauseEnum::DEPRESSION);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::DEATH_PLAYER);
        }
    }
}
