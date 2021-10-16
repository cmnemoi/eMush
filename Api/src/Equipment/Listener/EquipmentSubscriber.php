<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEventInterface;
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
            EquipmentEventInterface::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEventInterface::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEventInterface::EQUIPMENT_TRANSFORM => 'onEquipmentTransform',
        ];
    }

    public function onEquipmentCreated(EquipmentEventInterface $event): void
    {
        $player = $event->getPlayer();
        $place = $event->getPlace();
        $equipment = $event->getEquipment();

        if ($player === null ||
            !$equipment instanceof GameItem ||
            $player->getItems()->count() >= $this->getGameConfig($equipment)->getMaxItemInInventory()
        ) {
            $equipment->setPlace($place);
        } else {
            $equipment->setPlayer($player);
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();
        $equipment->removeLocation();

        $this->gameEquipmentService->delete($equipment);
    }

    public function onEquipmentTransform(EquipmentEventInterface $event): void
    {
        $equipment = $event->getEquipment();
        $place = $equipment->getCurrentPlace();
        $player = $event->getPlayer();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if ($newEquipment instanceof GameItem && $player !== null && $player->getItems()->count() < $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $newEquipment->setPlayer($player);
        } else {
            $newEquipment->setPlace($place);
        }

        $this->gameEquipmentService->delete($equipment);
        $this->gameEquipmentService->persist($newEquipment);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
