<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler;

    public function __construct(
        EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
    ) {
        $this->equipmentCycleHandler = $equipmentCycleHandler;
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

        /** @var EquipmentMechanic $mechanic */
        foreach ($equipment->getEquipment()->getMechanics() as $mechanic) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanic)) {
                $cycleHandler->handleNewCycle($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }

    public function onNewDay(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        /** @var EquipmentMechanic $mechanics */
        foreach ($equipment->getEquipment()->getMechanics() as $mechanics) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanics)) {
                $cycleHandler->handleNewDay($equipment, $event->getDaedalus(), $event->getTime());
            }
        }
    }
}
