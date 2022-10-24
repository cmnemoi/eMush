<?php

namespace Mush\RoomLog\Listener;

use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
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

    private function createEventLog(string $logKey, PlayerEvent $event): void
    {
        $player = $event->getPlayer();
        $logParameters = $event->getLogParameters();

        if ($logKey == LogEnum::DEATH) {
            if (!($reason = $event->getReason())) {
                throw new \LogicException('Player should die with a reason');
            }

            $logParameters[LanguageEnum::END_CAUSE] = $reason;
        }

        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            VisibilityEnum::PUBLIC,
            'event_log',
            $player,
            $logParameters,
            $event->getTime()
        );
    }
}
