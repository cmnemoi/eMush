<?php

namespace Mush\Game\ConfigData;

use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Equipment\Entity\Dto\WeaponEffect\BreakWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\DestroyWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\DropWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictInjuryWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\InflictRandomInjuryWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\ModifyDamageWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\ModifyMaxDamageWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\MultiplyDamageOnMushTargetWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\OneShotWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\RemoveActionPointsWeaponEffectConfigDto;
use Mush\Equipment\Entity\Dto\WeaponEffect\WeaponEffectDto;
use Mush\Equipment\Entity\Dto\WeaponEventConfigDto;
use Mush\Equipment\Enum\WeaponEffectEnum;
use Mush\Equipment\Enum\WeaponEventEnum;
use Mush\Equipment\Enum\WeaponEventType;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;

/** @codeCoverageIgnore */
class EventConfigData
{
    public const string CHANGE_VARIABLE_PLAYER_PLUS_1_ACTION_POINT = 'change.variable_player_+1_actionPoint';
    public const string CHANGE_VALUE_PLUS_1_MAX_DAEDALUS_SPORE = 'change.value.max_daedalus_+1_spore';
    public const string CHANGE_VALUE_PLUS_1_CHARGE_MUSH_STATUS = 'change.value.max_mush_status_+1_charge';
    public const string CHANGE_VARIABLE_PLAYER_MINUS_1_SPORE = 'change.variable_player_-1_spore';
    public const string CHANGE_VARIABLE_PLAYER_PLUS_1_MOVEMENT_POINT = 'change.variable_player_+1_movementPoint';
    public const string CHANGE_VALUE_PLUS_2_MAX_PRIVATE_CHANNELS = 'change.value.max_private_channels_+2';
    public const string CHANGE_VALUE_PLUS_1_MAX_PLAYER_SPORE = 'change.value.max_player_+1_spore';
    public const string CHANGE_VALUE_MINUS_2_MAX_DAEDALUS_SPORES = 'change.value.max_daedalus_-2_spores';
    public const string CHANGE_VARIABLE_TURRET_MAX_CHARGE_4 = 'change.variable_turret_max_charge_+4';
    public const string CHANGE_VARIABLE_TURRET_CHARGE_8 = 'change.variable_turret_charge_+8';
    public const string CHANGE_VARIABLE_PLAYER_PLUS_3_MORALE_POINT = 'change.variable_player_+3_moralePoint';

    public static array $variableEventConfigData = [
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-1_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-4_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-2_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_1_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-3_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -5,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-5_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -12,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_-12_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-1_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-2_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -3,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-3_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -4,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-4_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -6,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-6_healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-1_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-2_moralPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_1_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-1_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-2_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-1_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-2_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_1_movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::SATIETY,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_-1_satiety',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::SATIETY,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_1_satiety',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 4,
            'targetVariable' => EquipmentStatusEnum::ELECTRIC_CHARGES,
            'variableHolderClass' => ModifierHolderClassEnum::EQUIPMENT,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VARIABLE_TURRET_MAX_CHARGE_4,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 5,
            'targetVariable' => DaedalusVariableEnum::SHIELD,
            'variableHolderClass' => ModifierHolderClassEnum::DAEDALUS,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_daedalus_shield_+5',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::HEALTH_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_+1healthPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_+1moralePoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 2,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => 'change.variable_player_+2movementPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 6,
            'targetVariable' => EquipmentStatusEnum::ELECTRIC_CHARGES,
            'variableHolderClass' => ModifierHolderClassEnum::EQUIPMENT,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.variable_patrol_ship_max_charges_+6',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1_000_000,
            'targetVariable' => EquipmentStatusEnum::ELECTRIC_CHARGES,
            'variableHolderClass' => ModifierHolderClassEnum::EQUIPMENT,
            'eventName' => VariableEventInterface::SET_VALUE,
            'name' => 'change.variable_patrol_ship_set_charges_to_maximum',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 2,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => 'change.value.max_player_+2_actionPoint',
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::ACTION_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => self::CHANGE_VARIABLE_PLAYER_PLUS_1_ACTION_POINT,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => DaedalusVariableEnum::SPORE,
            'variableHolderClass' => ModifierHolderClassEnum::DAEDALUS,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VALUE_PLUS_1_MAX_DAEDALUS_SPORE,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerStatusEnum::MUSH,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VALUE_PLUS_1_CHARGE_MUSH_STATUS,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -1,
            'targetVariable' => PlayerVariableEnum::SPORE,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => self::CHANGE_VARIABLE_PLAYER_MINUS_1_SPORE,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => PlayerVariableEnum::MOVEMENT_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => self::CHANGE_VARIABLE_PLAYER_PLUS_1_MOVEMENT_POINT,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 2,
            'targetVariable' => PlayerVariableEnum::PRIVATE_CHANNELS,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VALUE_PLUS_2_MAX_PRIVATE_CHANNELS,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 1,
            'targetVariable' => DaedalusVariableEnum::SPORE,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VALUE_PLUS_1_MAX_PLAYER_SPORE,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => -2,
            'targetVariable' => DaedalusVariableEnum::SPORE,
            'variableHolderClass' => ModifierHolderClassEnum::DAEDALUS,
            'eventName' => VariableEventInterface::CHANGE_VALUE_MAX,
            'name' => self::CHANGE_VALUE_MINUS_2_MAX_DAEDALUS_SPORES,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 8,
            'targetVariable' => EquipmentStatusEnum::ELECTRIC_CHARGES,
            'variableHolderClass' => ModifierHolderClassEnum::EQUIPMENT,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => self::CHANGE_VARIABLE_TURRET_CHARGE_8,
        ],
        [
            'type' => 'variable_event_config',
            'quantity' => 3,
            'targetVariable' => PlayerVariableEnum::MORAL_POINT,
            'variableHolderClass' => ModifierHolderClassEnum::PLAYER,
            'eventName' => VariableEventInterface::CHANGE_VARIABLE,
            'name' => self::CHANGE_VARIABLE_PLAYER_PLUS_3_MORALE_POINT,
        ],
    ];

    public static array $planetSectorEventConfigData = [
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::ACCIDENT_3_5,
            'eventName' => PlanetSectorEvent::ACCIDENT,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                3 => 1,
                4 => 1,
                5 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::DISASTER_3_5,
            'eventName' => PlanetSectorEvent::DISASTER,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                3 => 1,
                4 => 1,
                5 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::NOTHING_TO_REPORT,
            'eventName' => PlanetSectorEvent::NOTHING_TO_REPORT,
            'outputQuantity' => [0 => 1],
            'outputTable' => [0 => 1],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::TIRED_2,
            'eventName' => PlanetSectorEvent::TIRED,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                2 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::OXYGEN_8,
            'eventName' => PlanetSectorEvent::OXYGEN,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                8 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::OXYGEN_16,
            'eventName' => PlanetSectorEvent::OXYGEN,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                16 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::OXYGEN_24,
            'eventName' => PlanetSectorEvent::OXYGEN,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                24 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_1,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [],
            'outputTable' => [
                1 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_2,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                2 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_3,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                3 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_4,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                4 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_5,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                5 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => PlanetSectorEvent::FUEL_6,
            'eventName' => PlanetSectorEvent::FUEL,
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                6 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'artefact',
            'eventName' => 'artefact',
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                'alien_bottle_opener' => 1,
                'alien_holographic_tv' => 1,
                'invertebrate_shell' => 1,
                'jar_of_alien_oil' => 1,
                'magellan_liquid_map' => 1,
                'printed_circuit_jelly' => 1,
                'rolling_boulder' => 1,
                'starmap_fragment' => 1,
                'water_stick' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'kill_random',
            'eventName' => 'kill_random',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'kill_all',
            'eventName' => 'kill_all',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'provision_1',
            'eventName' => 'provision',
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                'alien_steak' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'provision_2',
            'eventName' => 'provision',
            'outputQuantity' => [2 => 1],
            'outputTable' => [
                'alien_steak' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'provision_3',
            'eventName' => 'provision',
            'outputQuantity' => [3 => 1],
            'outputTable' => [
                'alien_steak' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'provision_4',
            'eventName' => 'provision',
            'outputQuantity' => [4 => 1],
            'outputTable' => [
                'alien_steak' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_8',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                8 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_10',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                10 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_12',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                12 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_15',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                15 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_18',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                18 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_32',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                32 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'fight_8_10_12_15_18_32',
            'eventName' => 'fight',
            'outputQuantity' => [5 => 1],
            'outputTable' => [
                8 => 1,
                10 => 1,
                12 => 1,
                15 => 1,
                18 => 1,
                32 => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'harvest_1',
            'eventName' => 'harvest',
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                'creepnut' => 1,
                'meztine' => 1,
                'guntiflop' => 1,
                'ploshmina' => 1,
                'precati' => 1,
                'bottine' => 1,
                'fragilane' => 1,
                'anemole' => 1,
                'peniraft' => 1,
                'kubinus' => 1,
                'caleboot' => 1,
                'filandra' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'harvest_2',
            'eventName' => 'harvest',
            'outputQuantity' => [2 => 1],
            'outputTable' => [
                'creepnut' => 1,
                'meztine' => 1,
                'guntiflop' => 1,
                'ploshmina' => 1,
                'precati' => 1,
                'bottine' => 1,
                'fragilane' => 1,
                'anemole' => 1,
                'peniraft' => 1,
                'kubinus' => 1,
                'caleboot' => 1,
                'filandra' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'harvest_3',
            'eventName' => 'harvest',
            'outputQuantity' => [3 => 1],
            'outputTable' => [
                'creepnut' => 1,
                'meztine' => 1,
                'guntiflop' => 1,
                'ploshmina' => 1,
                'precati' => 1,
                'bottine' => 1,
                'fragilane' => 1,
                'anemole' => 1,
                'peniraft' => 1,
                'kubinus' => 1,
                'caleboot' => 1,
                'filandra' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'disease',
            'eventName' => 'disease',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'starmap',
            'eventName' => 'starmap',
            'outputQuantity' => [1 => 1],
            'outputTable' => [
                'starmap_fragment' => 1,
            ],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'mush_trap',
            'eventName' => 'mush_trap',
            'outputQuantity' => [50 => 1],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'again',
            'eventName' => 'again',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'item_lost',
            'eventName' => 'item_lost',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'back',
            'eventName' => 'back',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'player_lost',
            'eventName' => 'player_lost',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'find_lost',
            'eventName' => 'find_lost',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
        [
            'type' => 'planet_sector_event_config',
            'name' => 'kill_lost',
            'eventName' => 'kill_lost',
            'outputQuantity' => [],
            'outputTable' => [],
        ],
    ];

    /** @return WeaponEventConfigDto[] */
    public static function weaponEventConfigData(): array
    {
        return [
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString(),
                eventName: WeaponEventEnum::BLASTER_SUCCESSFUL_SHOT->toString(),
                eventType: WeaponEventType::NORMAL,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_FAILED_SHOT->toString(),
                eventName: WeaponEventEnum::BLASTER_FAILED_SHOT->toString(),
                eventType: WeaponEventType::MISS,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::BLASTER_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_DROP_WEAPON->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_DROP_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_SHOOTER_DROP_WEAPON_SHOOTER_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_MINUS_1_AP_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_DAMAGED_EARS->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_ONE_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_MASHED_EAR_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_DAMAGE_TARGET_30_TORN_TONGUE_TARGET_30_BURST_NOSE_TARGET_30_OPEN_AIR_BRAIN_TARGET_30_HEAD_TRAUMA->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_TORN_TONGUE_INJURY_TO_TARGET_30_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_BURST_NOSE_INJURY_TO_TARGET_30_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_OPEN_AIR_BRAIN_INJURY_TO_TARGET_30_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_HEAD_TRAUMA_INJURY_TO_TARGET_30_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_PLUS_2_MAX_DAMAGE_20_RANDOM_INJURY_TO_TARGET->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_MAX_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET_20_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString(),
                eventName: WeaponEventEnum::BLASTER_SHOOTER_PLUS_1_DAMAGE_TARGET_REMOVE_2_AP->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_ONE_DAMAGE->toString(),
                    WeaponEffectEnum::REMOVE_TWO_ACTION_POINTS_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString(),
                eventName: WeaponEventEnum::BLASTER_TARGET_HEADSHOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::BLASTER_ONE_SHOT->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::BLASTER_TARGET_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SUCCESSFUL_SHOT->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SUCCESSFUL_SHOT->toString(),
                eventType: WeaponEventType::NORMAL,
                effectKeys: [
                    WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_10_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_HEADSHOT->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_HEADSHOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::BIG_GUN_ONE_SHOT->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                    WeaponEffectEnum::ADD_TWO_MAX_DAMAGE->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_TARGET_MINUS_1AP->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_TARGET_MINUS_1AP->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_HEADSHOT_2->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_HEADSHOT_2->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::NATAMY_RIFLE_INJURY_ONE_SHOT->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_TARGET_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_TARGET_MASHED_FOOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BURNT_HAND->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BURNT_HAND->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BURNT_HAND_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BROKEN_SHOULDER->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_BROKEN_SHOULDER->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_FAILED_SHOT->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_FAILED_SHOT->toString(),
                eventType: WeaponEventType::MISS,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_MASHED_FOOT->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_DROP_WEAPON->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_DROP_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventName: WeaponEventEnum::NATAMY_RIFLE_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->toString(),
                eventName: WeaponEventEnum::KNIFE_SUCCESSFUL_HIT_10_MINOR_HAEMORRHAGE->toString(),
                eventType: WeaponEventType::NORMAL,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MINOR_HAEMORRHAGE_TO_TARGET_10_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_50_CRITICAL_HAEMORRHAGE->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_50_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_50_CRITICAL_HAEMORRHAGE_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_50_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_60_CRITICAL_HAEMORRHAGE_BUSTED_ARM_JOINT->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_60_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_BUSTED_ARM_JOINT_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_INSTAGIB_BLED->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::KNIFE_ONE_SHOT->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_PUNCTURED_LUNG->toString(),
                eventName: WeaponEventEnum::KNIFE_PLUS_2_DAMAGE_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                    WeaponEffectEnum::INFLICT_PUNCTURED_LUNG_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_FAILED_HIT->toString(),
                eventName: WeaponEventEnum::KNIFE_FAILED_HIT->toString(),
                eventType: WeaponEventType::MISS,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::KNIFE_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_BREAK_WEAPON_SHOOTER_TORN_TONGUE->toString(),
                eventName: WeaponEventEnum::KNIFE_BREAK_WEAPON_SHOOTER_TORN_TONGUE->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                    WeaponEffectEnum::INFLICT_TORN_TONGUE_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_SHOOTER_BRUISED_SHOULDER->toString(),
                eventName: WeaponEventEnum::KNIFE_SHOOTER_BRUISED_SHOULDER->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BRUISED_SHOULDER_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_SHOOTER_DROP_WEAPON->toString(),
                eventName: WeaponEventEnum::KNIFE_SHOOTER_DROP_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::KNIFE_SHOOTER_MINUS_2_AP->toString(),
                eventName: WeaponEventEnum::KNIFE_SHOOTER_MINUS_2_AP->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_TWO_ACTION_POINTS_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SUCCESSFUL_SHOT->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SUCCESSFUL_SHOT->toString(),
                eventType: WeaponEventType::NORMAL,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_10_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_HEADSHOT->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_HEADSHOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::BIG_GUN_ONE_SHOT->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_MAX_DAMAGE->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_TARGET_MINUS_1AP->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_TARGET_MINUS_1AP->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_HEADSHOT_2->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::OLD_FAITHFUL_INJURY_ONE_SHOT->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_TARGET_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_TARGET_MASHED_FOOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BURNT_HAND->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BURNT_HAND->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BURNT_HAND_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BROKEN_SHOULDER->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_BROKEN_SHOULDER->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_FAILED_SHOT->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_FAILED_SHOT->toString(),
                eventType: WeaponEventType::MISS,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_MASHED_FOOT->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_DROP_WEAPON->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_DROP_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventName: WeaponEventEnum::OLD_FAITHFUL_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SUCCESSFUL_SHOT->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SUCCESSFUL_SHOT->toString(),
                eventType: WeaponEventType::NORMAL,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_10_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::BIG_GUN_ONE_SHOT->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_PLUS_2_MAX_DAMAGE_SHOOTER_MINUS_1_AP_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_RANDOM_INJURY->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_MAX_DAMAGE->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_TARGET_MINUS_1AP->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_TARGET_MINUS_1AP->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT_2->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_HEADSHOT_2->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::LIZARO_JUNGLE_INJURY_ONE_SHOT->toString(),
                    WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_TARGET_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_TARGET_MASHED_FOOT->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_INJURY_TO_TARGET->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_TARGET_BROKEN_SHOULDER_TARGET_CRITICAL_HAEMORRHAGE_40_PERCENTS_TARGET_HAEMORRHAGE_40_PERCENTS->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_TARGET->toString(),
                    WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                    WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_BREAK_WEAPON->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_BREAK_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::BREAK_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_BURNT_HAND->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_BURNT_HAND->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BURNT_HAND_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_BROKEN_SHOULDER->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_BROKEN_SHOULDER->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_FAILED_SHOT->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_FAILED_SHOT->toString(),
                eventType: WeaponEventType::MISS,
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_MASHED_FOOT->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_MASHED_FOOT->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::INFLICT_MASHED_FOOT_TO_SHOOTER->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_DROP_WEAPON->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_DROP_WEAPON->toString(),
                eventType: WeaponEventType::FUMBLE,
                effectKeys: [
                    WeaponEffectEnum::DROP_WEAPON->toString(),
                ]
            ),
            new WeaponEventConfigDto(
                name: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventName: WeaponEventEnum::LIZARO_JUNGLE_SHOOTER_PLUS_2_DAMAGE->toString(),
                eventType: WeaponEventType::CRITIC,
                effectKeys: [
                    WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                ]
            ),
        ];
    }

    /** @return WeaponEffectDto[] */
    public static function weaponEffectsConfigData(): array
    {
        return [
            new RemoveActionPointsWeaponEffectConfigDto(
                name: WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::REMOVE_ACTION_POINTS->toString(),
                quantity: 1,
                toShooter: true,
            ),
            new RemoveActionPointsWeaponEffectConfigDto(
                name: WeaponEffectEnum::REMOVE_TWO_ACTION_POINTS_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::REMOVE_ACTION_POINTS->toString(),
                quantity: 2,
            ),
            new ModifyDamageWeaponEffectConfigDto(
                name: WeaponEffectEnum::ADD_ONE_DAMAGE->toString(),
                eventName: WeaponEffectEnum::MODIFY_DAMAGE->toString(),
                quantity: 1,
            ),
            new ModifyDamageWeaponEffectConfigDto(
                name: WeaponEffectEnum::ADD_TWO_DAMAGE->toString(),
                eventName: WeaponEffectEnum::MODIFY_DAMAGE->toString(),
                quantity: 2,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::BLASTER_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::BEHEADED,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::BIG_GUN_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::BEHEADED,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::NATAMY_RIFLE_INJURY_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::INJURY,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::OLD_FAITHFUL_INJURY_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::INJURY,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::LIZARO_JUNGLE_INJURY_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::INJURY,
            ),
            new InflictRandomInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_RANDOM_INJURY->toString(),
                toShooter: true,
            ),
            new InflictRandomInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_RANDOM_INJURY->toString(),
            ),
            new InflictRandomInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_RANDOM_INJURY_TO_TARGET_20_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_RANDOM_INJURY->toString(),
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_MASHED_EAR_INJURY_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::DAMAGED_EARS,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_TORN_TONGUE_INJURY_TO_TARGET_30_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::TORN_TONGUE,
                triggerRate: 30,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BURST_NOSE_INJURY_TO_TARGET_30_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BURST_NOSE,
                triggerRate: 30,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_OPEN_AIR_BRAIN_INJURY_TO_TARGET_30_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::OPEN_AIR_BRAIN,
                triggerRate: 30,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_HEAD_TRAUMA_INJURY_TO_TARGET_30_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::HEAD_TRAUMA,
                triggerRate: 30,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::HAEMORRHAGE,
                triggerRate: 40,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_10_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::CRITICAL_HAEMORRHAGE,
                triggerRate: 10,
            ),
            new ModifyMaxDamageWeaponEffectConfigDto(
                name: WeaponEffectEnum::ADD_TWO_MAX_DAMAGE->toString(),
                eventName: WeaponEffectEnum::MODIFY_MAX_DAMAGE->toString(),
                quantity: 2,
            ),
            new BreakWeaponEffectConfigDto(
                name: WeaponEffectEnum::BREAK_WEAPON->toString(),
                eventName: WeaponEffectEnum::BREAK_WEAPON->toString(),
            ),
            new DropWeaponEffectConfigDto(
                name: WeaponEffectEnum::DROP_WEAPON->toString(),
                eventName: WeaponEffectEnum::DROP_WEAPON->toString(),
            ),
            new DestroyWeaponEffectConfigDto(
                name: WeaponEffectEnum::DESTROY_WEAPON->toString(),
                eventName: WeaponEffectEnum::DESTROY_WEAPON->toString(),
            ),
            new MultiplyDamageOnMushTargetWeaponEffectConfigDto(
                name: WeaponEffectEnum::DOUBLE_DAMAGE_ON_MUSH_TARGET->toString(),
                eventName: WeaponEffectEnum::MULTIPLY_DAMAGE_ON_MUSH_TARGET->toString(),
                quantity: 2,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_40_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::CRITICAL_HAEMORRHAGE,
                triggerRate: 40,
            ),
            new RemoveActionPointsWeaponEffectConfigDto(
                name: WeaponEffectEnum::REMOVE_ONE_ACTION_POINT_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::REMOVE_ACTION_POINTS->toString(),
                quantity: 1,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_MASHED_FOOT_INJURY_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::MASHED_FOOT,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BROKEN_SHOULDER,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BURNT_HAND_INJURY_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BURNT_HAND,
                toShooter: true,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BROKEN_SHOULDER_INJURY_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BROKEN_SHOULDER,
                toShooter: true,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_MASHED_FOOT_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::MASHED_FOOT,
                toShooter: true,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_MINOR_HAEMORRHAGE_TO_TARGET_10_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::MINOR_HAEMORRHAGE,
                triggerRate: 10,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_50_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::CRITICAL_HAEMORRHAGE,
                triggerRate: 50,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_CRITICAL_HAEMORRHAGE_INJURY_TO_TARGET_60_PERCENTS->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::CRITICAL_HAEMORRHAGE,
                triggerRate: 60,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BUSTED_ARM_JOINT_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BUSTED_ARM_JOINT,
            ),
            new OneShotWeaponEffectConfigDto(
                name: WeaponEffectEnum::KNIFE_ONE_SHOT->toString(),
                eventName: WeaponEffectEnum::ONE_SHOT->toString(),
                endCause: EndCauseEnum::BLED,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_PUNCTURED_LUNG_TO_TARGET->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::PUNCTURED_LUNG,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_TORN_TONGUE_INJURY_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::TORN_TONGUE,
                toShooter: true,
            ),
            new InflictInjuryWeaponEffectConfigDto(
                name: WeaponEffectEnum::INFLICT_BRUISED_SHOULDER_INJURY_TO_SHOOTER->toString(),
                eventName: WeaponEffectEnum::INFLICT_INJURY->toString(),
                injuryName: InjuryEnum::BRUISED_SHOULDER,
                toShooter: true,
            ),
        ];
    }

    public static function getWeaponEventConfigByName(string $name): WeaponEventConfigDto
    {
        try {
            return current(array_filter(self::weaponEventConfigData(), static fn (WeaponEventConfigDto $dto) => $dto->name === $name));
        } catch (\Throwable $e) {
            throw new \RuntimeException("WeaponEventConfig not found for name {$name}");
        }
    }

    public static function getWeaponEffectConfigDataByName(WeaponEffectEnum $name): WeaponEffectDto
    {
        $result = current(array_filter(self::weaponEffectsConfigData(), static fn (WeaponEffectDto $dto) => $dto->name === $name->toString()));
        if (!$result) {
            throw new \RuntimeException("WeaponEffectConfig not found for name {$name->toString()}");
        }

        return $result;
    }
}
