<?php

namespace Mush\Equipment\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
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
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onEquipmentCreated', 1000], //this is done before everything else as the newGameEquipment is created here
                ['onOverflowingInventory', -1000],
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => ['onEquipmentDestroyed', -1000], //the equipment is deleted after every other effect has been applied
            EquipmentEvent::EQUIPMENT_TRANSFORM => [
                ['onEquipmentCreated', 1000], //this is done before everything else as the newGameEquipment is created here
                ['onEquipmentDestroyed', -1000], //the equipment is deleted after every other effect has been applied
                ['onOverflowingInventory', -1001],
            ],
            EquipmentEvent::CHANGE_HOLDER => ['onChangeHolder', -1000], //the equipment is deleted after every other effect has been applied
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $holder = $event->getHolder();
        $equipmentName = $event->getEquipmentName();

        $newEquipment = $this->gameEquipmentService->createGameEquipmentFromName($equipmentName, $holder, $event->getReason(), $event->getTime());
        $event->setNewEquipment($newEquipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getExistingEquipment();

        if ($equipment === null) {
            throw new \LogicException('ExistingEquipment should be provided for this event');
        }

        $equipment->setHolder(null);
        $this->gameEquipmentService->delete($equipment);
    }

    public function onOverflowingInventory(EquipmentEvent $event): void
    {
        $holder = $event->getHolder();
        $newEquipment = $event->getNewEquipment();

        if ($newEquipment === null) {
            throw new \LogicException('New Equipment should be provided for this event');
        }

        if ($holder instanceof Player &&
            $holder->getEquipments()->count() > $this->getGameConfig($newEquipment)->getMaxItemInInventory()
        ) {
            $newEquipment->setHolder($holder->getPlace());
            $this->gameEquipmentService->persist($newEquipment);
        }
    }

    public function onChangeHolder(EquipmentEvent $event): void
    {
        $holder = $event->getHolder();
        $existingEquipment = $event->getExistingEquipment();

        if ($existingEquipment === null) {
            throw new \LogicException('ExistingEquipment should be provided for this event');
        }

        $existingEquipment->setHolder($holder);

        $this->gameEquipmentService->persist($existingEquipment);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
