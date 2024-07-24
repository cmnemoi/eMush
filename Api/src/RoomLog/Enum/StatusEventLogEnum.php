<?php

namespace Mush\RoomLog\Enum;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Skill\Enum\SkillName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\StatusEvent;

abstract class StatusEventLogEnum
{
    public const SOILED = 'soiled';
    public const BECOME_PREGNANT = 'become_pregnant';
    public const EQUIPMENT_BROKEN = 'equipment_broken';
    public const STUCK_IN_THE_SHIP = 'stuck_in_the_ship';
    public const GET_UP_BED_BROKEN = 'get_up_bed_broken';
    public const GAIN_SHOOT_POINT = 'gain_shoot_point';
    public const LOST_IN_EXPLORATION = 'lost_in_exploration';
    public const PLAYER_FALL_ASLEEP = 'player_fall_asleep';
    public const PLAYER_WAKE_UP = 'player_wake_up';

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
            PlayerStatusEnum::INACTIVE => self::PLAYER_FALL_ASLEEP,
        ],
        StatusEvent::STATUS_REMOVED => [
            EquipmentStatusEnum::PLANT_YOUNG => PlantLogEnum::PLANT_MATURITY,
            PlayerStatusEnum::LYING_DOWN => self::GET_UP_BED_BROKEN,
            PlayerStatusEnum::INACTIVE => self::PLAYER_WAKE_UP,
            PlayerStatusEnum::HIGHLY_INACTIVE => self::PLAYER_WAKE_UP,
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
            SkillName::SHOOTER->value => self::GAIN_SHOOT_POINT,
        ],
        self::LOSS => [],
    ];
}
