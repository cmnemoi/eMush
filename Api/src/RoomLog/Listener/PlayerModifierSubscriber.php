<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents()
    {
        return [
            PlayerModifierEvent::ACTION_POINT_MODIFIER => 'onActionPointModifier',
            PlayerModifierEvent::MOVEMENT_POINT_MODIFIER => 'onMovementPointModifier',
            PlayerModifierEvent::HEALTH_POINT_MODIFIER => 'onHealthPointModifier',
            PlayerModifierEvent::MORAL_POINT_MODIFIER => 'onMoralPointModifier',
        ];
    }

    public function onActionPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $delta = $playerEvent->getQuantity();

        if ($delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT;

        $this->roomLogService->createLog(
            $logKey,
            $playerEvent->getPlace(),
            $playerEvent->getVisibility(),
            'event_log',
            $playerEvent->getPlayer(),
            ['quantity' => $delta],
        );
    }

    public function onMovementPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $delta = $playerEvent->getQuantity();

        if ($delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT;

        $this->roomLogService->createLog(
            $logKey,
            $playerEvent->getPlace(),
            $playerEvent->getVisibility(),
            'event_log',
            $playerEvent->getPlayer(),
            ['quantity' => $delta],
        );
    }

    public function onHealthPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $delta = $playerEvent->getQuantity();

        if ($delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT;
        $this->roomLogService->createLog(
            $logKey,
            $playerEvent->getPlace(),
            $playerEvent->getVisibility(),
            'event_log',
            $playerEvent->getPlayer(),
            ['quantity' => $delta],
        );
    }

    public function onMoralPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $delta = $playerEvent->getQuantity();

        if ($delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT;
        $this->roomLogService->createLog(
            $logKey,
            $playerEvent->getPlace(),
            $playerEvent->getVisibility(),
            'event_log',
            $playerEvent->getPlayer(),
            ['quantity' => $delta],
        );
    }
}
