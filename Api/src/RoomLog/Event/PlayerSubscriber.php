<?php

namespace Mush\RoomLog\Event;

use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerSubscriber implements EventSubscriberInterface
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
            PlayerEvent::NEW_PLAYER => 'onNewPlayer',
            PlayerEvent::DEATH_PLAYER => ['onDeathPlayer', 10],
            PlayerEvent::METAL_PLATE => 'onMetalPlate',
            PlayerEvent::PANIC_CRISIS => 'onPanicCrisis',
        ];
    }

    public function onNewPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            LogEnum::AWAKEN,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            null,
            null,
            null,
            $event->getTime()
        );
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            LogEnum::DEATH,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            null,
            null,
            null,
            $event->getTime()
        );
    }

    public function onMetalPlate(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            LogEnum::METAL_PLATE,
            $player->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            null,
            null,
            null,
            $event->getTime()
        );
    }

    public function onPanicCrisis(PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            LogEnum::PANIC_CRISIS,
            $player->getPlace(),
            VisibilityEnum::PRIVATE,
            'event_log',
            $player,
            null,
            null,
            null,
            $event->getTime()
        );
    }
}
