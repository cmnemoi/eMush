<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\DayEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaySubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * DaedalusSubscriber constructor.
     */
    public function __construct(DaedalusServiceInterface $daedalusService, EventDispatcherInterface $eventDispatcher)
    {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onNewDay(DayEvent $event)
    {
        if ($event->getGameItem() || $event->getPlayer() || $event->getRoom() || $event->getStatus()) {
            return;
        }
        $daedalus = $event->getDaedalus();
        foreach ($daedalus->getPlayers() as $player) {
            $newPlayerDay = new DayEvent($daedalus, $event->getTime());
            $newPlayerDay->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerDay, DayEvent::NEW_DAY);
        }

        foreach ($daedalus->getRooms() as $room) {
            $newRoomDay = new DayEvent($daedalus, $event->getTime());
            $newRoomDay->setRoom($room);
            $this->eventDispatcher->dispatch($newRoomDay, DayEvent::NEW_DAY);
        }
    }
}
