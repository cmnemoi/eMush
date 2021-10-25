<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
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
        $this->createEventLog(LogEnum::AWAKEN, $event);
    }

    public function onDeathPlayer(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::DEATH, $event);
    }

    public function onMetalPlate(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::METAL_PLATE, $event);
    }

    public function onPanicCrisis(PlayerEvent $event): void
    {
        $this->createEventLog(LogEnum::PANIC_CRISIS, $event);
    }

    private function createEventLog(string $logKey, PlayerEvent $event): void
    {
        $player = $event->getPlayer();

        $this->roomLogService->createLog(
            $logKey,
            $player->getPlace(),
            $event->getVisibility(),
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
