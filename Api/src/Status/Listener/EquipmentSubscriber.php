<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_TRANSFORM => ['onEquipmentTransform', 1000], // change the status before original equipment is destroyed
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
        ];
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $newEquipment = $event->getNewEquipment();
        $oldEquipment = $event->getExistingEquipment();

        if ($oldEquipment === null || $newEquipment === null) {
            throw new \LogicException('2 equipments should be provided');
        }

        /** @var Status $status */
        foreach ($oldEquipment->getStatuses() as $status) {
            $newEquipment->addStatus($status);
            $this->statusService->persist($status);
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment();

        if ($equipment === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        $this->statusService->removeAllStatus($equipment, $event->getReason(), $event->getTime());
    }
}
