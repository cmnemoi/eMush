<?php

namespace Mush\Daedalus\Event;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\CycleEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CycleSubscriber implements EventSubscriberInterface
{
    private DaedalusServiceInterface $daedalusService;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * DaedalusSubscriber constructor.
     * @param DaedalusServiceInterface $daedalusService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(DaedalusServiceInterface $daedalusService, EventDispatcherInterface $eventDispatcher)
    {
        $this->daedalusService = $daedalusService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents()
    {
        return [
            CycleEvent::NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(CycleEvent $event)
    {
        if (!($daedalus = $event->getDaedalus())) {
            return;
        }

        foreach ($daedalus->getPlayers() as $player) {
            $newPlayerCycle = new CycleEvent($event->getTime());
            $newPlayerCycle->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerCycle, CycleEvent::NEW_CYCLE);
        }

        foreach ($daedalus->getRooms() as $room) {
            $newRoomCycle = new CycleEvent($event->getTime());
            $newRoomCycle->setRoom($room);
            $this->eventDispatcher->dispatch($newRoomCycle, CycleEvent::NEW_CYCLE);
        }
    }
}