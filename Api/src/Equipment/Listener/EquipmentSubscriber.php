<?php

namespace Mush\Equipment\Listener;

use Error;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEvent::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        if (!$player = $event->getPlayer()) {
            throw new Error('Player should be provided');
        }

        $equipment = $event->getEquipment();

        if (!$equipment instanceof GameItem) {
            $equipment->setPlace($player->getPlace());
        } elseif ($player->getItems()->count() < $this->getGameConfig($equipment)->getMaxItemInInventory()) {
            $equipment->setPlayer($player);
        } else {
            $equipment->setPlace($player->getPlace());
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if (($brokenStatus = $equipment->getStatusByName(EquipmentStatusEnum::BROKEN)) === null) {
            throw new \LogicException('equipment should be broken to be fixed');
        }

        $equipment->removeStatus($brokenStatus);
        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->statusService->createCoreStatus(EquipmentStatusEnum::BROKEN, $equipment);
        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->gameEquipmentService->delete($equipment);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
