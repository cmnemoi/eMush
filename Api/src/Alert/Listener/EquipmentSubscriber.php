<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
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
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
            EquipmentEvent::EQUIPMENT_TRANSFORM => [
                ['onEquipmentTransform'],
            ],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        if ($equipment->isBroken()) {
            $alert = $this->alertService->findByNameAndDaedalus(
                AlertEnum::BROKEN_EQUIPMENTS,
                $event->getPlace()->getDaedalus()
            );

            if ($alert === null) {
                throw new \LogicException('there should be a broken alert on this Daedalus');
            }

            $alertElement = $this->alertService->getAlertEquipmentElement($alert, $equipment);
            $this->alertService->deleteAlertElement($alertElement);
        }
    }

    public function onEquipmentTransform(TransformEquipmentEvent $event): void
    {
        $newEquipment = $event->getGameEquipment();
        $oldEquipment = $event->getEquipmentFrom();

        if ($oldEquipment->isBroken()) {
            $alert = $this->alertService->findByNameAndDaedalus(AlertEnum::BROKEN_EQUIPMENTS, $event->getPlace()->getDaedalus());

            if ($alert === null) {
                throw new \LogicException('there should be a broken alert on this Daedalus');
            }

            $alertElement = $this->alertService->getAlertEquipmentElement($alert, $oldEquipment);
            $alertElement->setEquipment($newEquipment);
        }
    }
}
