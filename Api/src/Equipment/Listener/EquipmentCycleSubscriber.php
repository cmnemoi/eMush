<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Enum\EventEnum;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DroneTasksHandler $droneTasksHandler,
        private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        /** @var EquipmentMechanic $mechanics */
        foreach ($equipment->getEquipment()->getMechanics() as $mechanics) {
            foreach ($mechanics->getMechanics() as $mechanicName) {
                if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanicName)) {
                    if ($event->hasTag(EventEnum::NEW_DAY)) {
                        $cycleHandler->handleNewDay($equipment, $event->getTime());
                    }

                    $cycleHandler->handleNewCycle($equipment, $event->getTime());
                }
            }
        }

        if ($equipment instanceof Drone) {
            $this->droneTasksHandler->execute($equipment, $event->getTime());
        }
    }
}
