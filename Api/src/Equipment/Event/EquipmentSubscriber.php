<?php

namespace Mush\Equipment\Event;

use Error;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEvent::CONSUME_CHARGE => 'onConsumeCharge',
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
            $this->roomLogService->createEquipmentLog(
                LogEnum::OBJECT_FELT,
                $player->getPlace(),
                $player,
                $equipment,
                VisibilityEnum::PUBLIC,
                $event->getTime()
            );
        }

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $this->statusService->createCoreStatus(EquipmentStatusEnum::BROKEN, $equipment);

        $this->gameEquipmentService->persist($equipment);

        if ($equipment instanceof Door) {
            $rooms = $equipment->getRooms()->toArray();
        } else {
            $rooms = [$equipment->getCurrentPlace()];
        }

        foreach ($rooms as $room) {
            $this->roomLogService->createEquipmentLog(
                LogEnum::EQUIPMENT_BROKEN,
                $room,
                null,
                $equipment,
                $event->getVisibility(),
                $event->getTime()
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $place = $equipment->getCurrentPlace();
        $equipment->removeLocation();

        $this->gameEquipmentService->delete($equipment);

        $this->roomLogService->createEquipmentLog(
            LogEnum::EQUIPMENT_DESTROYED,
            $place,
            null,
            $equipment,
            $event->getVisibility(),
            $event->getTime()
        );
    }

    public function onConsumeCharge(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $chargeStatus = $equipment->getStatusByName(EquipmentStatusEnum::CHARGES);

        if (!($chargeStatus !== null &&
            $chargeStatus instanceof ChargeStatus &&
            $chargeStatus->getCharge() > 0)
        ) {
            throw new Error('Equipment should have a charge status with more than 0 charge');
        }

        $chargeStatus->addCharge(-1);

        if ($chargeStatus->isAutoRemove() &&
            ($threshold = $chargeStatus->getThreshold()) &&
            $chargeStatus->getCharge() === $threshold
        ) {
            $equipmentEvent = new EquipmentEvent($equipment, VisibilityEnum::HIDDEN);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        $this->statusService->persist($chargeStatus);
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
