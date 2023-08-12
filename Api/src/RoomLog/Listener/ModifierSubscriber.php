<?php

namespace Mush\RoomLog\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModifierSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModifierEvent::APPLY_MODIFIER => 'onApplyModifier',
        ];
    }

    public function onApplyModifier(ModifierEvent $event): void
    {
        if (($logKey = $event->mapLog(LogEnum::MODIFIER_LOG_ENUM[LogEnum::VALUE])) !== null) {
            $logVisibility = $event->mapLog(LogEnum::MODIFIER_LOG_ENUM[LogEnum::VISIBILITY]) ?: VisibilityEnum::HIDDEN;

            $this->createEventLog($logKey, $logVisibility, $event);
        }
    }

    private function createEventLog(string $logKey, string $logVisibility, ModifierEvent $event): void
    {
        $modifier = $event->getModifier();
        $holder = $modifier->getModifierHolder();
        $player = null;

        switch (true) {
            case $holder instanceof Player:
                $player = $holder;
                $place = $holder->getPlace();
                $parameters = [$player->getLogKey() => $player->getLogName()];
                break;
            case $holder instanceof Place:
                $place = $holder;
                $parameters = [];
                break;
            case $holder instanceof GameEquipment:
                $place = $holder->getPlace();
                $parameters = [$holder->getLogKey() => $holder->getLogName()];
                break;
            case $holder instanceof Daedalus:
            default:
                return;
        }

        $this->roomLogService->createLog(
            $logKey,
            $place,
            $logVisibility,
            'event_log',
            $player,
            $parameters,
            $event->getTime()
        );
    }
}
