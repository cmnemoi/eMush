<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
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
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEvent::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment->hasStatus(EquipmentStatusEnum::BROKEN)) {
            $this->alertService->handleEquipmentRepair($equipment);
        }
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentBreak($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getPlace()->getDaedalus(), true);
        }
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentRepair($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getPlace()->getDaedalus(), false);
        }
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if ($equipment->isBroken()) {
            $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $equipment->getPlace()->getDaedalus());

            if ($alert === null) {
                throw new \LogicException('there should be a broken alert on this Daedalus');
            }

            $alertElement = $this->alertService->getAlertEquipmentElement($alert, $equipment);

            $alertElement->setEquipment($newEquipment);
        }
    }
}
