<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DroneTasksHandler $droneTasksHandler,
        private EventServiceInterface $eventService,
        private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
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

        $equipmentMechanics = $equipment->getAllMechanicsAndEquipmentName();

        foreach ($equipmentMechanics as $mechanic) {
            if ($cycleHandler = $this->equipmentCycleHandler->getEquipmentCycleHandler($mechanic)) {
                if ($event->hasTag(EventEnum::NEW_DAY)) {
                    $cycleHandler->handleNewDay($equipment, $event->getTime());
                }
                $cycleHandler->handleNewCycle($equipment, $event->getTime());
            }
        }

        if ($equipment instanceof Drone) {
            $this->droneTasksHandler->execute($equipment, $event->getTime());
        }
    }
}
