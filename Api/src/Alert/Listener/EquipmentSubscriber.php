<?php

namespace Mush\Alert\Listener;

use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
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
            EquipmentEvent::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $newEquipment = $event->getNewEquipment();
        $oldEquipment = $event->getExistingEquipment();

        if ($newEquipment === null || $oldEquipment === null) {
            throw new \LogicException('2 equipments should be provided');
        }

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
