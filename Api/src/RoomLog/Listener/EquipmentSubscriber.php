<?php

namespace Mush\RoomLog\Listener;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_CREATED => [['onEquipmentCreated', -100], ['onInventoryOverflow']],
            EquipmentEvent::EQUIPMENT_DESTROYED => 'onEquipmentDestroyed',
            EquipmentEvent::EQUIPMENT_TRANSFORM => 'onInventoryOverflow',
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $newEquipment = $event->getNewEquipment();
        $holder = $event->getHolder();

        if ($newEquipment === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if ($event->getReason() === EventEnum::PLANT_PRODUCTION) {
            $this->roomLogService->createLog(
                PlantLogEnum::PLANT_NEW_FRUIT,
                $event->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                null,
                $event->getLogParameters(),
                $event->getTime()
            );
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        if ($event->getVisibility() !== VisibilityEnum::HIDDEN) {
            $this->roomLogService->createLog(
                LogEnum::EQUIPMENT_DESTROYED,
                $event->getPlace(),
                $event->getVisibility(),
                'event_log',
                null,
                $event->getLogParameters(),
                $event->getTime()
            );
        }
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $holder = $event->getHolder();

        if (($newEquipment = $event->getNewEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        if (
            $newEquipment instanceof GameItem &&
            $holder instanceof Player &&
            $holder->getEquipments()->count() > $this->getGameConfig($newEquipment)->getMaxItemInInventory()
        ) {
            $this->roomLogService->createLog(
                LogEnum::OBJECT_FELT,
                $event->getPlace(),
                VisibilityEnum::PUBLIC,
                'event_log',
                $holder,
                $event->getLogParameters(),
                $event->getTime()
            );
        }
    }

    private function getGameConfig(GameEquipment $gameEquipment): GameConfig
    {
        return $gameEquipment->getEquipment()->getGameConfig();
    }
}
