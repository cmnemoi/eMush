<?php

namespace Mush\Status\Listener;

use Mush\Player\Event\PlayerCycleEvent;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
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
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }

    public function onNewDay(PlayerCycleEvent $event): void
    {
        $player = $event->getPlayer();

        foreach ($player->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $player,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
