<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Modifier\Service\EquipmentModifierServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $gearModifierService;
    private EquipmentModifierServiceInterface $equipmentModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $gearModifierService,
        EquipmentModifierServiceInterface $equipmentModifierService
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->equipmentModifierService = $equipmentModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed', 10], // change in modifier must be applied before the item is totally removed
            ],
            EquipmentEvent::EQUIPMENT_TRANSFORM => [
                ['onEquipmentDestroyed'],
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onInventoryOverflow', 100],
            ],
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onEquipmentCreated', 100],
            ],
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $holder = $event->getEquipment()->getHolder();

        if ($holder instanceof Player) {
            $this->equipmentModifierService->takeEquipment($equipment, $holder);
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        if ($event instanceof TransformEquipmentEvent) {
            $equipment = $event->getEquipmentFrom();
        } else {
            $equipment = $event->getEquipment();
        }

        $this->gearModifierService->gearDestroyed($equipment);
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $holder = $equipment->getHolder();
        $gameConfig = $holder->getPlace()->getDaedalus()->getGameConfig();

        if (
            $equipment instanceof GameItem &&
            $holder instanceof Player &&
            $holder->getEquipments()->count() > $gameConfig->getMaxItemInInventory()
        ) {
            $this->gearModifierService->dropEquipment($equipment, $holder);
        }
    }
}
