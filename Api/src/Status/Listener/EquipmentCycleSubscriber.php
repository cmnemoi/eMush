<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\Status;
use Mush\Status\Event\StatusCycleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    private EventServiceInterface $eventService;

    public function __construct(
        EventServiceInterface $eventService
    ) {
        $this->eventService = $eventService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => ['onNewCycle', 10],
            EquipmentCycleEvent::EQUIPMENT_NEW_DAY => ['onNewDay', 10],
        ];
    }

    public function onNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        /** @var Status $status */
        foreach ($equipment->getStatuses() as $status) {
            $statusNewCycle = new StatusCycleEvent(
                $status,
                $equipment,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewCycle, StatusCycleEvent::STATUS_NEW_CYCLE);
        }
    }

    public function onNewDay(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        /** @var Status $status */
        foreach ($equipment->getStatuses() as $status) {
            $statusNewDay = new StatusCycleEvent(
                $status,
                $equipment,
                $event->getReasons()[0],
                $event->getTime()
            );
            $this->eventService->callEvent($statusNewDay, StatusCycleEvent::STATUS_NEW_DAY);
        }
    }
}
