<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Event\WeaponFiredEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class WeaponFiredEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WeaponFiredEvent::class => 'onWeaponFired',
        ];
    }

    public function onWeaponFired(WeaponFiredEvent $event): void
    {
        $attacker = $event->getAttacker();
        $target = $event->getTarget();

        $this->roomLogService->createLog(
            logKey: $event->getName(),
            place: $event->getAttacker()->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'weapon_event',
            player: $event->getAttacker(),
            parameters: [
                $attacker->getLogKey() => $attacker->getLogName(),
                'target_' . $target->getLogKey() => $target->getLogName(),
            ],
        );
    }
}
