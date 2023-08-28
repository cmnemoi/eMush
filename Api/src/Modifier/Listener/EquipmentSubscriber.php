<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\TransformEquipmentEvent;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
    ) {
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
            EquipmentEvent::CHANGE_HOLDER => [
                ['onEquipmentRemovedFromInventory', 2000],
                ['onNewEquipmentInInventory', -2000],
            ],
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        if ($event instanceof TransformEquipmentEvent) {
            $equipment = $event->getEquipmentFrom();
        } else {
            $equipment = $event->getGameEquipment();
        }

        $this->equipmentModifierService->gearDestroyed($equipment, $event->getTags(), $event->getTime());
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();
        $holder = $equipment->getHolder();

        if (
            $equipment instanceof GameItem
            && $holder instanceof Player
            && $holder->getEquipments()->count() > $holder->getPlayerInfo()->getCharacterConfig()->getMaxItemInInventory()
        ) {
            $this->equipmentModifierService->dropEquipment($equipment, $holder, $event->getTags(), $event->getTime());
        }
    }

    public function onNewEquipmentInInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        $player = $equipment->getHolder();
        if ($player instanceof Player) {
            $this->equipmentModifierService->takeEquipment($equipment, $player, $event->getTags(), $event->getTime());
        }
    }

    public function onEquipmentRemovedFromInventory(EquipmentEvent $event): void
    {
        $equipment = $event->getGameEquipment();

        $player = $equipment->getHolder();
        if ($player instanceof Player) {
            $this->equipmentModifierService->dropEquipment($equipment, $player, $event->getTags(), $event->getTime());
        }
    }
}
