<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Equipment\Entity\Config\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEvent::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $player = $event->getPlayer();
        $place = $event->getPlace();
        $equipment = $event->getEquipment();

        if ($player === null ||
            !$equipment instanceof GameItem ||
            $player->getEquipments()->count() >= $this->getGameConfig($equipment)->getMaxItemInInventory()
        ) {
            $equipment->setHolder($place);
        } else {
            $equipment->setHolder($player);
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $equipment->setHolder(null);

        $this->gameEquipmentService->delete($equipment);
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $place = $equipment->getPlace();
        $player = $event->getPlayer();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if ($newEquipment instanceof GameItem && $player !== null && $player->getEquipments()->count() - 1 < $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $newEquipment->setHolder($player);
        } else {
            $newEquipment->setHolder($place);
        }

        $this->gameEquipmentService->delete($equipment);
        $this->gameEquipmentService->persist($newEquipment);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
