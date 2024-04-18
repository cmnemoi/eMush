<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Event\EquipmentCycleEvent;
use Mush\Equipment\Service\EquipmentCycleHandlerServiceInterface;
use Mush\Game\Enum\EventEnum;
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
            EquipmentCycleEvent::EQUIPMENT_NEW_CYCLE => 'onNewCycle',
        ];
    }

    public function onNewCycle(EquipmentCycleEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        // @var EquipmentMechanic $mechanic
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
    }
}
