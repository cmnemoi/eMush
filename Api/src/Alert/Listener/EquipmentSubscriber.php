<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private AlertServiceInterface $alertService;

    public function __construct(
        AlertServiceInterface $alertService
    ) {
        $this->alertService = $alertService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
        ];
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentBreak($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getCurrentPlace()->getDaedalus(), true);
        }
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentRepair($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getCurrentPlace()->getDaedalus(), false);
        }
    }
}
