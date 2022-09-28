<?php

namespace Mush\Player\Listener;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Event\PlayerCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
          $this->eventDispatcher = $eventDispatcher;
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
            $this->eventDispatcher->dispatch($newPlayerCycle, PlayerCycleEvent::PLAYER_NEW_CYCLE);
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

            $this->eventDispatcher->dispatch($newPlayerDay, PlayerCycleEvent::PLAYER_NEW_DAY);
        }
    }
}
