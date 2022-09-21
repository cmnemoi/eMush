<?php

namespace Mush\RoomLog\Enum;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierNameEnum;

class LogEnum
{
    public const AWAKEN = 'awaken';
    public const DEATH = 'death';
    public const OBJECT_FELL = 'object_fell';
    public const SOIL_PREVENTED_OCD = 'soil_prevented_ocd';
    public const OXY_LOW_USE_CAPSULE = 'oxy_low_use_capsule';
    public const TREMOR_NO_GRAVITY = 'tremor_no_gravity';
    public const TREMOR_GRAVITY = 'tremor_gravity';
    public const ELECTRIC_ARC = 'electric_arc';
    public const METAL_PLATE = 'metal_plate';
    public const EQUIPMENT_DESTROYED = 'equipment_destroyed';
    public const GARDEN_DESTROYED = 'garden_destroyed';
    public const FORCE_GET_UP = 'force_get_up';

    public const CONSUME_MUSH = 'consume_mush';
    public const SOIL_PREVENTED = 'soil_prevented';
    public const HELP_DISABLED = 'help_disabled';
    public const CLUMSINESS_PREVENTED = 'clumsiness_prevented';

    public const DISORDER_APPEAR = 'disorder_appear';
    public const DISEASE_APPEAR = 'disease_appear';
    public const DISEASE_CURED = 'disease_cured';
    public const DISEASE_TREATED = 'disease_treated';
    public const DISEASE_CURED_DRUG = 'disease_cured_drug';
    public const DISEASE_TREATED_DRUG = 'disease_treated_drug';
    public const DISEASE_CURED_PLAYER = 'disease_cured_player';
    public const DISEASE_TREATED_PLAYER = 'disease_treated_player';
    public const DISORDER_CURED = 'disorder_cured';
    public const DISORDER_TREATED = 'disorder_treated';
    public const INJURY_APPEAR = 'injury_appear';
    public const SURGERY_SUCCESS = 'surgery_success';
    public const SURGERY_CRITICAL_SUCCESS = 'surgery_critical_success';
    public const SELF_SURGERY_SUCCESS = 'self_surgery_success';
    public const SELF_SURGERY_CRITICAL_SUCCESS = 'self_surgery_critical_success';
    public const TRAUMA_DISEASE = 'trauma_disease';

    public const VISIBILITY = 'visibility';
    public const VALUE = 'value';

    public const MODIFIER_LOG_ENUM = [
        ModifierNameEnum::MUSH_SATIETY_CONSUME => [
            self::VISIBILITY => VisibilityEnum::PRIVATE,
            self::VALUE => self::CONSUME_MUSH,
        ],
        ModifierNameEnum::DISABLED_MOVE_MODIFIER => [
            self::VISIBILITY => VisibilityEnum::PUBLIC,
            self::VALUE => self::HELP_DISABLED,
        ],
        ModifierNameEnum::APRON_MODIFIER => [
            self::VISIBILITY => VisibilityEnum::PRIVATE,
            self::VALUE => self::SOIL_PREVENTED,
        ],
        ModifierNameEnum::GLOVES_MODIFIER => [
            self::VISIBILITY => VisibilityEnum::PRIVATE,
            self::VALUE => self::CLUMSINESS_PREVENTED,
        ],
    ];

    public static function getSurgeryLogs(): array
    {
        return [
            self::SURGERY_CRITICAL_SUCCESS,
            self::SURGERY_SUCCESS,
            self::SELF_SURGERY_CRITICAL_SUCCESS,
            self::SELF_SURGERY_SUCCESS,
        ];
    }
}
