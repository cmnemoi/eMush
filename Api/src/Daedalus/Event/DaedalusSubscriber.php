<?php


namespace Mush\Player\Event;

use Mush\Daedalus\Event\DaedalusEvent;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Event\CycleEvent;
use Mush\Game\Event\DayEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusSubscriber implements EventSubscriberInterface
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
            DaedalusEvent::NEW_DAEDALUS => 'onDaedalusNew',
            DaedalusEvent::END_DAEDALUS => 'onDaedalusEnd',
            DaedalusEvent::FULL_DAEDALUS => 'onDaedalusFull',
            CycleEvent::NEW_CYCLE => 'onNewCycle',
            DayEvent::NEW_DAY => 'onNewDay',
        ];
    }

    public function onDaedalusNew(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
    }

    public function onDaedalusEnd(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs
    }

    public function onDaedalusFull(DaedalusEvent $event)
    {
        $daedalus = $event->getDaedalus();
        // @TODO: create logs
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

        $this->daedalusService->persist($daedalus);
    }

    public function onNewDay(DayEvent $event)
    {
        if (!($daedalus = $event->getDaedalus())) {
            return;
        }

        foreach ($daedalus->getPlayers() as $player) {
            $newPlayerDay = new DayEvent($event->getTime());
            $newPlayerDay->setPlayer($player);
            $this->eventDispatcher->dispatch($newPlayerDay, DayEvent::NEW_DAY);
        }

        $this->daedalusService->persist($daedalus);
    }
}