<?php

namespace Mush\RoomLog\Enum;

use Mush\Action\Enum\ActionEnum;
use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Enum\ModifierNameEnum;

class LogEnum
{
    public const string AWAKEN = 'awaken';
    public const string DEATH = 'death';
    public const string OBJECT_FELL = 'object_fell';
    public const string SOIL_PREVENTED_OCD = 'soil_prevented_ocd';
    public const string OXY_LOW_USE_CAPSULE = 'oxy_low_use_capsule';
    public const string TREMOR_NO_GRAVITY = 'tremor_no_gravity';
    public const string TREMOR_GRAVITY = 'tremor_gravity';
    public const string ELECTRIC_ARC = 'electric_arc';
    public const string METAL_PLATE = 'metal_plate';
    public const string EQUIPMENT_DESTROYED = 'equipment_destroyed';
    public const string GARDEN_DESTROYED = 'garden_destroyed';
    public const string FORCE_GET_UP = 'force_get_up';
    public const string CONSUME_MUSH = 'consume_mush';
    public const string SOIL_PREVENTED = 'soil_prevented';
    public const string HELP_DISABLED = 'help_disabled';
    public const string CLUMSINESS_PREVENTED = 'clumsiness_prevented';
    public const string DISORDER_APPEAR = 'disorder_appear';
    public const string DISEASE_APPEAR = 'disease_appear';
    public const string DISEASE_CURED = 'disease_cured';
    public const string DISEASE_TREATED = 'disease_treated';
    public const string DISEASE_CURED_DRUG = 'disease_cured_drug';
    public const string DISEASE_TREATED_DRUG = 'disease_treated_drug';
    public const string DISEASE_CURED_PLAYER = 'disease_cured_player';
    public const string DISEASE_TREATED_PLAYER = 'disease_treated_player';
    public const string DISORDER_CURED = 'disorder_cured';
    public const string DISORDER_TREATED = 'disorder_treated';
    public const string INJURY_APPEAR = 'injury_appear';
    public const string SURGERY_SUCCESS = 'surgery_success';
    public const string SURGERY_CRITICAL_SUCCESS = 'surgery_critical_success';
    public const string SELF_SURGERY_SUCCESS = 'self_surgery_success';
    public const string SELF_SURGERY_CRITICAL_SUCCESS = 'self_surgery_critical_success';
    public const string TRAUMA_DISEASE = 'trauma_disease';
    public const string DISEASE_OVERRIDDEN = 'disease_overridden';
    public const string HUNTER_DEATH_TURRET = 'hunter_death_turret';
    public const string HUNTER_DEATH_PATROL_SHIP = 'hunter_death_patrol_ship';
    public const string SCRAP_COLLECTED = 'scrap_collected';
    public const string ATTACKED_BY_HUNTER = 'attacked_by_hunter';
    public const string PATROL_DISCHARGE = 'patrol_discharge';
    public const string PATROL_DAMAGE = 'patrol_damage';
    public const string LIQUID_MAP_HELPED = 'liquid_map_helped';
    public const string EXPLORATION_FINISHED = 'exploration_finished';
    public const string ALL_EXPLORATORS_STUCKED = 'all_explorators_stucked';
    public const string ALL_EXPLORATORS_DEAD = 'all_explorators_dead';
    public const string FOUND_ITEM_IN_EXPLORATION = 'found_item_in_exploration';
    public const string DISEASE_BY_ALIEN_FIGHT = 'disease_by_alien_fight';
    public const string DISEASE_BY_ALIEN_TRAVEL = 'disease_by_alien_travel';
    public const string INVERTEBRATE_SHELL_EXPLOSION = 'invertebrate_shell_explosion';
    public const string LOST_ITEM_IN_EXPLORATION = 'lost_item_in_exploration';
    public const string VISIBILITY = 'visibility';
    public const string VALUE = 'value';
    public const array MODIFIER_LOG_ENUM = [
        self::VISIBILITY => [
            ModifierNameEnum::MUSH_CONSUME => VisibilityEnum::PRIVATE,
            ModifierNameEnum::DISABLED_MOVE_MODIFIER => VisibilityEnum::PUBLIC,
            ModifierNameEnum::APRON_MODIFIER => VisibilityEnum::PRIVATE,
            ModifierNameEnum::GLOVES_MODIFIER => VisibilityEnum::PRIVATE,
            SymptomEnum::BITING => VisibilityEnum::HIDDEN,
            SymptomEnum::BREAKOUTS => VisibilityEnum::PUBLIC,
            SymptomEnum::CAT_ALLERGY => VisibilityEnum::PUBLIC,
            SymptomEnum::DIRTINESS => VisibilityEnum::HIDDEN,
            SymptomEnum::DROOLING => VisibilityEnum::PUBLIC,
            SymptomEnum::FEAR_OF_CATS => VisibilityEnum::PUBLIC,
            SymptomEnum::FOAMING_MOUTH => VisibilityEnum::PUBLIC,
            SymptomEnum::SNEEZING => VisibilityEnum::PUBLIC,
            SymptomEnum::VOMITING => VisibilityEnum::PUBLIC,
            ModifierNameEnum::LIQUID_MAP_MODIFIER => VisibilityEnum::PUBLIC,
        ],
        self::VALUE => [
            ModifierNameEnum::MUSH_CONSUME => self::CONSUME_MUSH,
            ModifierNameEnum::DISABLED_MOVE_MODIFIER => self::HELP_DISABLED,
            ModifierNameEnum::APRON_MODIFIER => self::SOIL_PREVENTED,
            ModifierNameEnum::GLOVES_MODIFIER => self::CLUMSINESS_PREVENTED,
            SymptomEnum::BITING => SymptomEnum::BITING,
            SymptomEnum::BREAKOUTS => SymptomEnum::BREAKOUTS,
            SymptomEnum::CAT_ALLERGY => SymptomEnum::CAT_ALLERGY,
            SymptomEnum::DIRTINESS => SymptomEnum::DIRTINESS,
            SymptomEnum::DROOLING => SymptomEnum::DROOLING,
            SymptomEnum::FEAR_OF_CATS => SymptomEnum::FEAR_OF_CATS,
            SymptomEnum::FOAMING_MOUTH => SymptomEnum::FOAMING_MOUTH,
            SymptomEnum::SNEEZING => SymptomEnum::SNEEZING,
            SymptomEnum::VOMITING => SymptomEnum::VOMITING,
            ModifierNameEnum::LIQUID_MAP_MODIFIER => self::LIQUID_MAP_HELPED,
        ],
    ];

    public const array HUNTER_DEATH_LOG_ENUM = [
        ActionEnum::SHOOT_HUNTER => self::HUNTER_DEATH_TURRET,
        ActionEnum::SHOOT_RANDOM_HUNTER => self::HUNTER_DEATH_TURRET,
        ActionEnum::SHOOT_HUNTER_PATROL_SHIP => self::HUNTER_DEATH_PATROL_SHIP,
        ActionEnum::SHOOT_RANDOM_HUNTER_PATROL_SHIP => self::HUNTER_DEATH_PATROL_SHIP,
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
