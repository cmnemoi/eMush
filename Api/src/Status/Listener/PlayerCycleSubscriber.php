<?php

namespace Mush\Status\Listener;

use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PlayerCycleEvent::PLAYER_NEW_CYCLE => 'onNewCycle',
            PlayerCycleEvent::PLAYER_NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewCycle(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $player,
                $player->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }

    public function onNewDay(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $player,
                $player->getDaedalus(),
                $event->getReason(),
                $event->getTime()
            );
            $this->eventDispatcher->dispatch($statusNewCycle, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
