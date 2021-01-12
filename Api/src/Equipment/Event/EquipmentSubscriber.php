<?php

namespace Mush\Equipment\Event;

use Error;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;
    private GameConfig $gameConfig;

    public function __construct(
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => 'onEquipmentCreated',
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
            $equipment->setRoom($player->getRoom());
        } elseif ($player->getItems()->count() < $this->gameConfig->getMaxItemInInventory()) {
            $equipment->setPlayer($player);
        } else {
            $equipment->setRoom($player->getRoom());
            $this->roomLogService->createEquipmentLog(
                LogEnum::OBJECT_FELT,
                $player->getRoom(),
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

        $this->statusService->createCoreEquipmentStatus(EquipmentStatusEnum::BROKEN, $equipment);

        $room = $equipment->getCurrentRoom();

        $this->roomLogService->createEquipmentLog(
            LogEnum::EQUIPMENT_BROKEN,
            $room,
            null,
            $equipment,
            $event->getVisibility(),
            $event->getTime()
        );

        $this->gameEquipmentService->persist($equipment);
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $room = $equipment->getCurrentRoom();
        $equipment->removeLocation();

        $this->gameEquipmentService->delete($equipment);


        $this->roomLogService->createEquipmentLog(
            LogEnum::EQUIPMENT_DESTROYED,
            $room,
            null,
            $equipment,
            $event->getVisibility(),
            $event->getTime()
        );
    }
}
