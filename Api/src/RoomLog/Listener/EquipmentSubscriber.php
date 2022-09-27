<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
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
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onEquipmentCreated', -1]
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onInventoryOverflow']
            ]
        ];
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        switch ($event->getReason()) {
            case EventEnum::PLANT_PRODUCTION:
                $logKey = PlantLogEnum::PLANT_NEW_FRUIT;
                break;

            case ActionEnum::BUILD:
                $logKey = ActionLogEnum::BUILD_SUCCESS;
                break;

            case ActionEnum::TRANSPLANT:
                $logKey = ActionLogEnum::TRANSPLANT_SUCCESS;
                break;

            case ActionEnum::OPEN:
                $logKey = ActionLogEnum::OPEN_SUCCESS;
                break;
            default:
                return;
        }

        $this->createEventLog($logKey, $event, $event->getVisibility());
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        switch ($event->getReason()) {
            case EventEnum::FIRE:
                $this->createEventLog(LogEnum::EQUIPMENT_DESTROYED, $event, VisibilityEnum::PUBLIC);

                return;
            case PlantLogEnum::PLANT_DEATH:
                $this->createEventLog(PlantLogEnum::PLANT_DEATH, $event, VisibilityEnum::PUBLIC);

                return;
        }
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        $holder = $event->getEquipment()->getHolder();
        $gameConfig = $holder->getPlace()->getDaedalus()->getGameConfig();
        $equipment = $event->getEquipment();

        if (
            $equipment instanceof GameItem &&
            $holder->getEquipments()->count() > $gameConfig->getMaxItemInInventory()
        ) {
            $this->createEventLog(LogEnum::OBJECT_FELL, $event, VisibilityEnum::PUBLIC);
        }
    }

    private function createEventLog(string $logKey, EquipmentEvent $event, string $visibility): void
    {
        if ($event instanceof InteractWithEquipmentEvent && $event->getActor() instanceof Player) {
            $player = $event->getActor();
        } else if ($event->isCreated()) {
            $player = $event->getEquipment()->getHolder();
        } else {
            $player = null;
        }

        $this->roomLogService->createLog(
            $logKey,
            $event->getPlace(),
            $visibility,
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
