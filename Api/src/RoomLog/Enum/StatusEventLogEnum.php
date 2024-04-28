<?php

namespace Mush\RoomLog\Enum;

use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

class StatusEventLogEnum
{
    public const SOILED = 'soiled';
    public const BECOME_PREGNANT = 'become_pregnant';
    public const EQUIPMENT_BROKEN = 'equipment_broken';
    public const STUCK_IN_THE_SHIP = 'stuck_in_the_ship';
    public const GET_UP_BED_BROKEN = 'get_up_bed_broken';
    public const GAIN_SHOOT_POINT = 'gain_shoot_point';
    public const LOST_IN_EXPLORATION = 'lost_in_exploration';

    public const VALUE = 'value';
    public const VISIBILITY = 'visibility';

    public const GAIN = 'gain';
    public const LOSS = 'loss';

    public const STATUS_EVENT_LOGS = [
        StatusEvent::STATUS_APPLIED => [
            PlayerStatusEnum::DIRTY => self::SOILED,
            PlayerStatusEnum::PREGNANT => self::BECOME_PREGNANT,
            EquipmentStatusEnum::BROKEN => self::EQUIPMENT_BROKEN,
            PlayerStatusEnum::STUCK_IN_THE_SHIP => self::STUCK_IN_THE_SHIP,
            PlayerStatusEnum::LOST => self::LOST_IN_EXPLORATION,
        ],
        StatusEvent::STATUS_REMOVED => [
            EquipmentStatusEnum::PLANT_YOUNG => PlantLogEnum::PLANT_MATURITY,
            PlayerStatusEnum::LYING_DOWN => self::GET_UP_BED_BROKEN,
        ],
        VariableEventInterface::CHANGE_VARIABLE => [
            self::VALUE => [
                HunterEvent::HUNTER_SHOT => LogEnum::PATROL_DAMAGE,
            ],
            self::VISIBILITY => [
                HunterEvent::HUNTER_SHOT => VisibilityEnum::PUBLIC,
            ],
        ],
    ];

    public const CHARGE_STATUS_UPDATED_LOGS = [
        self::GAIN => [
            SkillEnum::SHOOTER => self::GAIN_SHOOT_POINT,
        ],
        self::LOSS => [],
    ];
}
