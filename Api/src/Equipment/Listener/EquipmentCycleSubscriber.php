<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Npc;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\NPCTasks\AiHandler\CatTasksHandler;
use Mush\Equipment\NPCTasks\AiHandler\DogTasksHandler;
use Mush\Equipment\NPCTasks\AiHandler\DroneTasksHandler;
use Mush\Equipment\Repository\GameEquipmentRepositoryInterface;
use Mush\Equipment\Service\AiHandlerServiceInterface;
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
        private CatTasksHandler $catTasksHandler,
        private EventServiceInterface $eventService,
        private EquipmentCycleHandlerServiceInterface $equipmentCycleHandler,
        private GameEquipmentRepositoryInterface $gameEquipmentRepository,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
        private AiHandlerServiceInterface $aiHandlerServiceInterface,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => [
                ['onNewCycle', EventPriorityEnum::LOW],
                ['onNPCNewCycle', EventPriorityEnum::HIGH],
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

    public function onNPCNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment instanceof Npc) {
            $handler = $this->aiHandlerServiceInterface->getAiHandler($equipment->getAiHandler()->value);
            if ($handler) {
                $handler->execute($equipment, $event->getTime());
            }

        // TODO Delete after 31/01/2026
        } elseif ($equipment->getName() === ItemEnum::PAVLOV) {
            $this->dogTasksHandler->execute($equipment, $event->getTime());
        } elseif ($equipment->getName() === ItemEnum::SCHRODINGER) {
            $this->catTasksHandler->execute($equipment, $event->getTime());
        } elseif ($equipment->getName() === ItemEnum::SUPPORT_DRONE) {
            $this->droneTasksHandler->execute($equipment, $event->getTime());
        }
    }
}
