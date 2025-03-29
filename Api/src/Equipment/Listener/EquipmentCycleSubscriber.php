<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\NPCTasks\Pavlov\DogTasksHandler;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\EventPriorityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EquipmentCycleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DroneTasksHandler $droneTasksHandler,
        private DogTasksHandler $dogTasksHandler,
        private EventServiceInterface $eventService,
        private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => [
                ['onNewCycle', EventPriorityEnum::LOW],
                ['onDroneNewCycle', EventPriorityEnum::HIGH],
                ['onDogNewCycle', EventPriorityEnum::LOW],
            ],
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

                // some equipment may have been deleted by previous handler (e.g. dried out plant)
                // so we don't want to handle cycle for them
                if (!$equipment->isNull()) {
                    $cycleHandler->handleNewCycle($equipment, $event->getTime());
                }
            }
        }
    }

    public function onDroneNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment instanceof Drone) {
            $this->droneTasksHandler->execute($equipment, $event->getTime());
        }
    }

    public function onDogNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment->getName() === ItemEnum::PAVLOV) {
            $this->dogTasksHandler->execute($equipment, $event->getTime());
        }
    }
}
