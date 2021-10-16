<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
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
            $player->getItems()->count() >= $this->getGameConfig($equipment)->getMaxItemInInventory()
        ) {
            $equipment->setPlace($place);
        } else {
            $equipment->setPlayer($player);
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();
        $equipment->removeLocation();

        $this->gameEquipmentService->delete($equipment);
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
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
