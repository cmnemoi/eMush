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
            $this->roomLogService->createLog(
                LogEnum::OBJECT_FELT,
                $player->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $player,
                $equipment,
                null,
                $event->getTime()
            );
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

        if ($equipment instanceof Door) {
            $rooms = $equipment->getRooms()->toArray();
        } else {
            $rooms = [$equipment->getCurrentPlace()];
        }

        foreach ($rooms as $room) {
            $this->roomLogService->createLog(
                LogEnum::EQUIPMENT_BROKEN,
                $room,
                $event->getVisibility(),
                'event_log',
                null,
                $equipment,
                null,
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

        $this->roomLogService->createLog(
            LogEnum::EQUIPMENT_DESTROYED,
            $place,
            $event->getVisibility(),
            'event_log',
            null,
            $equipment,
            null,
            $event->getTime()
        );
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
