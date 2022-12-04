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

    public function __construct(
        EquipmentModifierServiceInterface $gearModifierService
    ) {
        $this->gearModifierService = $gearModifierService;
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
        ];
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

        if (
            $equipment instanceof GameItem &&
            $holder instanceof Player &&
            $holder->getEquipments()->count() > $holder->getPlayerInfo()->getCharacterConfig()->getMaxItemInInventory()
        ) {
            $this->gearModifierService->dropEquipment($equipment, $holder);
        }
    }
}
