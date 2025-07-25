<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Actions\Takeoff;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEventReason;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Project\Enum\ProjectName;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private const CREATION_LOG_MAP = [
        EventEnum::PLANT_PRODUCTION => PlantLogEnum::PLANT_NEW_FRUIT,
        ActionEnum::BUILD->value => ActionLogEnum::BUILD_SUCCESS,
        ActionEnum::TRANSPLANT->value => ActionLogEnum::TRANSPLANT_SUCCESS,
        ActionEnum::OPEN->value => ActionLogEnum::OPEN_SUCCESS,
        LogEnum::FOUND_ITEM_IN_EXPLORATION => LogEnum::FOUND_ITEM_IN_EXPLORATION,
        ActionEnum::PRINT_ZE_LIST->value => LogEnum::TABULATRIX_PRINTS,
        EquipmentEventReason::AWAKEN_SCHRODINGER => LogEnum::AWAKEN_SCHRODINGER,
        ActionEnum::GEN_METAL->value => ActionLogEnum::GEN_METAL_SUCCESS,
        EquipmentEventReason::AWAKEN_PAVLOV => LogEnum::AWAKEN_PAVLOV,
    ];

    private const DESTRUCTION_LOG_MAP = [
        GearItemEnum::INVERTEBRATE_SHELL => LogEnum::INVERTEBRATE_SHELL_EXPLOSION,
        EventEnum::FIRE => LogEnum::EQUIPMENT_DESTROYED,
        PlantLogEnum::PLANT_DEATH => PlantLogEnum::PLANT_DEATH,
        EndCauseEnum::ASPHYXIA => LogEnum::OXY_LOW_USE_CAPSULE,
        PlanetSectorEvent::ITEM_LOST => LogEnum::LOST_ITEM_IN_EXPLORATION,
        ActionEnum::SHOOT_CAT->value => LogEnum::CAT_SHOT_DEAD,
        ActionEnum::ATTACK->value => LogEnum::EQUIPMENT_DESTROYED,
        ActionEnum::SHOOT->value => LogEnum::EQUIPMENT_DESTROYED,
        ActionEnum::THROW_GRENADE->value => LogEnum::EQUIPMENT_DESTROYED,
        LogEnum::FOOD_DESTROYED_BY_NERON => LogEnum::FOOD_DESTROYED_BY_NERON,
    ];

    private const MOVE_EQUIPMENT_LOG_MAP = [
        ActionEnum::COLLECT_SCRAP->value => LogEnum::SCRAP_COLLECTED,
        EventEnum::PRINT_DOCUMENT => LogEnum::TABULATRIX_PRINTS,
        ProjectName::FOOD_RETAILER->value => LogEnum::FRUIT_TRANSPORTED,
        Takeoff::DROP_CRITICAL_ITEM => LogEnum::DROP_SUCCESS,
        ActionEnum::CURE_CAT->value => LogEnum::DROP_SUCCESS,
    ];
    private RoomLogServiceInterface $roomLogService;

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::CHANGE_HOLDER => [
                ['onEquipmentChangeHolder'],
            ],
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onEquipmentCreated', 100],
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onInventoryOverflow'],
            ],
        ];
    }

    public function onEquipmentChangeHolder(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::MOVE_EQUIPMENT_LOG_MAP);

        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::CREATION_LOG_MAP);
        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::DESTRUCTION_LOG_MAP);

        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        /** @var Player $holder */
        $holder = $event->getGameEquipment()->getHolder();

        $characterConfig = $holder->getPlayerInfo()->getCharacterConfig();
        $equipment = $event->getGameEquipment();

        if (
            $equipment instanceof GameItem
            && $holder->getEquipments()->count() > $characterConfig->getMaxItemInInventory()
        ) {
            $this->createEventLog(LogEnum::OBJECT_FELL, $event, VisibilityEnum::PUBLIC);
        }
    }

    private function createEventLog(string $logKey, EquipmentEvent $event, string $visibility): void
    {
        // @var ?Player $player
        if ($event->getAuthor() instanceof Player) {
            $player = $event->getAuthor();
        } elseif ($event->isCreated()) {
            $holder = $event->getGameEquipment()->getHolder();
            if ($holder instanceof Player) {
                $player = $holder;
            } else {
                $player = null;
            }
        } else {
            $player = null;
        }

        $parameters = $event->getLogParameters();
        if ($player && !isset($parameters[$player->getLogKey()])) {
            $parameters[$player->getLogKey()] = $player->getLogName();
        }

        /** @var Place $logPlace */
        $logPlace = $event instanceof MoveEquipmentEvent ? $event->getNewHolder() : $event->getPlace();
        $this->roomLogService->createLog(
            $logKey,
            $logPlace,
            $visibility,
            'event_log',
            $player,
            $parameters,
            $event->getTime(),
        );

        // If event is fruit transport, also create a log in starting place
        if ($event->hasTag(ProjectName::FOOD_RETAILER->value)) {
            $this->roomLogService->createLog(
                $logKey,
                $event->getPlace(),
                $visibility,
                'event_log',
                $player,
                $parameters,
                $event->getTime(),
            );
        }
    }
}
