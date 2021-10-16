<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEventInterface;
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
            EquipmentEventInterface::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEventInterface::EQUIPMENT_BROKEN => 'onEquipmentBroken',
            EquipmentEventInterface::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEventInterface::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentDestroyed(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();

        if ($equipment->hasStatus(EquipmentStatusEnum::BROKEN)) {
            $this->alertService->handleEquipmentRepair($equipment);
        }
    }

    public function onEquipmentBroken(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentBreak($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getCurrentPlace()->getDaedalus(), true);
        }
    }

    public function onEquipmentFixed(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();

        $this->alertService->handleEquipmentRepair($equipment);

        if ($equipment->getName() === EquipmentEnum::GRAVITY_SIMULATOR) {
            $this->alertService->gravityAlert($equipment->getCurrentPlace()->getDaedalus(), false);
        }
    }

    public function onEquipmentTransform(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if ($equipment->isBroken()) {
            $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $equipment->getCurrentPlace()->getDaedalus());

            if ($alert === null) {
                throw new \LogicException('there should be a broken alert on this Daedalus');
            }

            $alertElement = $this->alertService->getAlertEquipmentElement($alert, $equipment);

            $alertElement->setEquipment($newEquipment);
        }
    }
}
