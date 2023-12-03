<?php

namespace Mush\Equipment\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DaedalusCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DaedalusCycleEvent::DAEDALUS_NEW_CYCLE => ['onNewCycle', EventPriorityEnum::EQUIPMENTS],
        ];
    }

    public function onNewCycle(DaedalusCycleEvent $event): void
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
