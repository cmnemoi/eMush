<?php

namespace Mush\Modifier\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Service\GearModifierServiceInterface;
use Mush\Player\Entity\Player;
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
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', 10], //change in modifier must be applied before the item is totally removed
            EquipmentEvent::EQUIPMENT_TRANSFORM => [['onEquipmentDestroyed'], ['onInventoryOverflow']],
            EquipmentEvent::EQUIPMENT_CREATED => 'onInventoryOverflow',
        ];
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment();
        if ($equipment === null) {
            throw new \LogicException('Equipment should be provided');
        }

        $this->gearModifierService->gearDestroyed($equipment);
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $holder = $event->getHolder();

        $newEquipment = $event->getNewEquipment();

        if ($newEquipment === null) {
            throw new \LogicException('New equipments should be provided');
        }

        if (
            $newEquipment instanceof GameItem &&
            $holder instanceof Player &&
            $holder->getEquipments()->count() > $this->getGameConfig($newEquipment)->getMaxItemInInventory()
        ) {
            $this->gearModifierService->dropGear($newEquipment, $holder);
        }
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
