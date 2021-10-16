<?php

namespace Mush\RoomLog\Listener;

use Mush\Player\Event\PlayerEventInterface;
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
            PlayerEventInterface::NEW_PLAYER => 'onNewPlayer',
            PlayerEventInterface::DEATH_PLAYER => ['onDeathPlayer', 10],
            PlayerEventInterface::METAL_PLATE => 'onMetalPlate',
            PlayerEventInterface::PANIC_CRISIS => 'onPanicCrisis',
        ];
    }

    public function onNewPlayer(PlayerEventInterface $event): void
    {
        $this->createEventLog(LogEnum::AWAKEN, $event);
    }

    public function onDeathPlayer(PlayerEventInterface $event): void
    {
        dump($event->getVisibility());
        $this->createEventLog(LogEnum::DEATH, $event);
    }

    public function onMetalPlate(PlayerEventInterface $event): void
    {
        $this->createEventLog(LogEnum::METAL_PLATE, $event);
    }

    public function onPanicCrisis(PlayerEventInterface $event): void
    {
        $this->createEventLog(LogEnum::PANIC_CRISIS, $event);
    }

    private function createEventLog(string $logKey, PlayerEventInterface $event): void
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
