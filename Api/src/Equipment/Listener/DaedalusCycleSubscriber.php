<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Lock\LockFactory;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;
    private LockFactory $lockFactory;

    public function __construct(EventServiceInterface $eventService, LockFactory $lockFactory)
    {
        $this->eventService = $eventService;
        $this->lockFactory = $lockFactory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::EQUIPMENTS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
    {
        $lock = $this->lockFactory->createLock('daedalus_cycle');
        $lock->acquire(true);

        try {
            $this->handleEquipmentNewCycle($event);
        } finally {
            $lock->release();
        }
    }

    private function handleEquipmentNewCycle($event): void
    {
        $daedalus = $event->getDaedalus();

        // first put all equipments in an array
        $equipmentsArray = [];
        $players = $daedalus->getPlayers()->getPlayerAlive();
        foreach ($players as $player) {
            $equipmentsArray = array_merge($equipmentsArray, $player->getEquipments()->toArray());
        }
        $places = $daedalus->getPlaces();
        foreach ($places as $place) {
            $equipmentsArray = array_merge($equipmentsArray, $place->getEquipments()->toArray());
        }
        $equipments = new ArrayCollection($equipmentsArray);

        foreach ($equipments as $equipment) {
            $itemNewCycle = new EquipmentCycleEvent(
                $equipment,
                $daedalus,
                $event->getTags(),
                $event->getTime()
            );
            $this->eventService->callEvent($itemNewCycle, EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE);
        }
    }
}
