<?php

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Event\Service\EventService;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventService $eventService;

    public function __construct(
        EventService $eventService
    ) {
          $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => 'onNewCycle',
            DaedalusCycleEvent::DAEDALUS_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerCycle = new PlayerCycleEvent(
                $player,
                $event->getReason(),
                $event->getTime()
            );
            $this->eventService->callEvent($newPlayerCycle, PlayerCycleEvent::PLAYER_NEW_CYCLE);
        }
    }

    public function onNewDay(DaedalusCycleEvent $event): void
    {
        foreach ($event->getDaedalus()->getPlayers()->getPlayerAlive() as $player) {
            $newPlayerDay = new PlayerCycleEvent(
                $player,
                $event->getReason(),
                $event->getTime()
            );

            $this->eventService->callEvent($newPlayerDay, PlayerCycleEvent::PLAYER_NEW_DAY);
        }
    }
}
