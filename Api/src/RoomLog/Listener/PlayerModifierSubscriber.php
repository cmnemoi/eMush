<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
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
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        if (!$playerEvent->isDisplayedRoomLog() || $delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_ACTION_POINT : LogEnum::LOSS_ACTION_POINT;
        $this->createPrivateLog($player, $logKey, $playerEvent->getTime(), abs($delta));
    }

    public function onMovementPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        if (!$playerEvent->isDisplayedRoomLog() || $delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_MOVEMENT_POINT : LogEnum::LOSS_MOVEMENT_POINT;
        $this->createPrivateLog($player, $logKey, $playerEvent->getTime(), abs($delta));
    }

    public function onHealthPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        if (!$playerEvent->isDisplayedRoomLog() || $delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_HEALTH_POINT : LogEnum::LOSS_HEALTH_POINT;
        $this->createPrivateLog($player, $logKey, $playerEvent->getTime(), abs($delta));
    }

    public function onMoralPointModifier(PlayerModifierEvent $playerEvent): void
    {
        $player = $playerEvent->getPlayer();
        $delta = $playerEvent->getDelta();

        if (!$playerEvent->isDisplayedRoomLog() || $delta === 0) {
            return;
        }

        $logKey = $delta > 0 ? LogEnum::GAIN_MORAL_POINT : LogEnum::LOSS_MORAL_POINT;
        $this->createPrivateLog($player, $logKey, $playerEvent->getTime(), abs($delta));
    }

    private function createPrivateLog(Player $player, string $logKey, \DateTime $time, ?int $quantity = null): void
    {
        $this->roomLogService->createLog(
            $logKey,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            ['quantity' => $quantity],
            $time
        );
    }
}
