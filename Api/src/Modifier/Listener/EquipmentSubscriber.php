<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Modifier\Service\GearModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GearModifierServiceInterface $gearModifierService;

    public function __construct(
        GearModifierServiceInterface $gearModifierService,
    ) {
        $this->gearModifierService = $gearModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => ['onEquipmentCreated', -100], //change in modifier must be applied after the item has been created
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', 10], //change in modifier must be applied before the item is totally removed
            EquipmentEvent::EQUIPMENT_TRANSFORM => [['onEquipmentTransformDestroy', 10], ['onEquipmentTransformCreate', -10]],
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->gearModifierService->gearCreated($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->gearModifierService->gearDestroyed($equipment);
    }

    public function onEquipmentTransformDestroy(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->gearModifierService->gearDestroyed($equipment);
    }

    public function onEquipmentTransformCreate(EquipmentEvent $event): void
    {
        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        $this->gearModifierService->gearCreated($newEquipment);
    }
}
