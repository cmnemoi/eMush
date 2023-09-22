<?php

namespace Mush\RoomLog\Listener;

use Mush\Hunter\Event\HunterEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HunterEventSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        RoomLogServiceInterface $roomLogService
    ) {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            HunterEvent::HUNTER_DEATH => 'onHunterDeath',
        ];
    }

    public function onHunterDeath(HunterEvent $event): void
    {
        $hunter = $event->getHunter();
        $player = $event->getAuthor();

        // if the event doesn't have a player, this means it's not a player which
        // killed the hunter (ie. asteroids hurting the Daedalus)
        // so there is no need to log it
        if (!$player) {
            return;
        }

        $logKey = $event->mapLog(LogEnum::HUNTER_DEATH_LOG_ENUM);
        if (!$logKey) {
            throw new \Exception('HunterEvent should have a logKey');
        }

        $this->roomLogService->createLog(
            $logKey,
            $player->getPlace(),
            $event->getVisibility(),
            'event_log',
            $player,
            [
                $hunter->getLogKey() => $hunter->getName(),
                $player->getLogKey() => $player->getLogName(),
            ],
            $event->getTime()
        );
    }
}
