<?php

namespace Mush\RoomLog\Enum;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Hunter\Event\HunterEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Event\StatusEvent;

abstract class StatusEventLogEnum
{
    public const string SOILED = 'soiled';
    public const string BECOME_PREGNANT = 'become_pregnant';
    public const string EQUIPMENT_BROKEN = 'equipment_broken';
    public const string STUCK_IN_THE_SHIP = 'stuck_in_the_ship';
    public const string GET_UP_BED_BROKEN = 'get_up_bed_broken';
    public const string GAIN_BOTANIST_POINT = 'gain_botanist_point';
    public const string GAIN_CONCEPTOR_POINT = 'gain_conceptor_point';
    public const string GAIN_IT_EXPERT_POINT = 'gain_it_expert_point';
    public const string GAIN_NURSE_POINT = 'gain_nurse_point';
    public const string GAIN_PILGRED_POINT = 'gain_pilgred_point';
    public const string GAIN_SHOOT_POINT = 'gain_shoot_point';
    public const string GAIN_TECHNICIAN_POINT = 'gain_technician_point';
    public const string LOST_IN_EXPLORATION = 'lost_in_exploration';
    public const string PLAYER_FALL_ASLEEP = 'player_fall_asleep';
    public const string PLAYER_WAKE_UP = 'player_wake_up';
    public const string CEASEFIRE_END = 'ceasefire_end';
    public const string SOILED_BY_MASS_GGEDON = 'soiled_by_mass_ggedon';

    public const string VALUE = 'value';
    public const string VISIBILITY = 'visibility';

    public const string GAIN = 'gain';
    public const string LOSS = 'loss';

    public const array STATUS_EVENT_LOGS = [
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
            PlaceStatusEnum::CEASEFIRE->value => self::CEASEFIRE_END,
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

    public const array CHARGE_STATUS_UPDATED_LOGS = [
        self::GAIN => [
            self::VALUE => [
                SkillPointsEnum::BOTANIST_POINTS->value => self::GAIN_BOTANIST_POINT,
                SkillPointsEnum::CONCEPTOR_POINTS->value => self::GAIN_CONCEPTOR_POINT,
                SkillPointsEnum::IT_EXPERT_POINTS->value => self::GAIN_IT_EXPERT_POINT,
                SkillPointsEnum::NURSE_POINTS->value => self::GAIN_NURSE_POINT,
                SkillPointsEnum::PILGRED_POINTS->value => self::GAIN_PILGRED_POINT,
                SkillPointsEnum::SHOOTER_POINTS->value => self::GAIN_SHOOT_POINT,
                SkillPointsEnum::TECHNICIAN_POINTS->value => self::GAIN_TECHNICIAN_POINT,
            ],
            self::VISIBILITY => [
                SkillPointsEnum::BOTANIST_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::CONCEPTOR_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::IT_EXPERT_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::NURSE_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::PILGRED_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::SHOOTER_POINTS->value => VisibilityEnum::PRIVATE,
                SkillPointsEnum::TECHNICIAN_POINTS->value => VisibilityEnum::PRIVATE,
            ],
        ],
        self::LOSS => [
            self::VALUE => [],
            self::VISIBILITY => [],
        ],
    ];
}
