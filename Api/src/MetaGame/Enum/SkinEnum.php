<?php

namespace Mush\MetaGame\Enum;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;

/**
 * Class enumerating the different skins for player, equipment and rooms.
 */
class SkinEnum
{
    public const string JANICE_SEXY = 'janice_sexy';
    public const string IAN_LABCOAT = 'ian_labcoat';
    public const string CHAO_LEATHER = 'chao_leather';
    public const string ROLAND_YULE = 'roland_yule';
    public const string ELEESHA_CLASSY = 'eleesha_classy';
    public const string RALUCA_ADDAMS = 'raluca_addams';
    public const string TERRENCE_BIKER = 'terrence_biker';
    public const string JIN_SU_GNANGNAN = 'jin_su_gnangnan';
    public const string HUA_PUPKIN = 'hua_pupkin';
    public const string JIN_SU_VAMPIRE = 'jin_su_vampire';
    public const string STEPHEN_SANTA = 'stephen_santa';
    public const string PAOLA_SANTA = 'paola_santa';
    public const string KUAN_ALBATOR = 'kuan_albator';
    public const string FINOLA_SKYWALKER = 'finola_skywalker';
    public const string GIOELE_BATHROBE = 'gioele_bathrobe';
    public const string CHUN_DALLAS = 'chun_dallas';
    public const string FRIEDA_GONADALF = 'frieda_gonadalf';

    // Equipment skins from projects and research
    public const string KITCHEN_APERO = 'kitchen_apero';
    public const string SCANNER_OVERCLOCKING = 'scanner_overclocking';
    public const string ANTENNA_SPATIAL_WAVE = 'antenna_spatial_waves';
    public const string TURRET_TESLA = 'turret_tesla';
    public const string TURRET_CHARGES = 'turret_charges';
    public const string COFFEE_MACHINE_FISSION = 'coffee_machine_fission';
    public const string COFFEE_MACHINE_GUARANA = 'coffee_machine_guarana';
    public const string ICARUS_LARGE = 'icarus_large';
    public const string ICARUS_THRUSTER = 'icarus_thruster';
    public const string NERON_CORE_PARTICIPATION = 'neron_core_participation';
    public const string NERON_PARTICIPATION_AUXILIARY_TERMINAL = 'neron_participation_auxiliary_terminal';
    public const string PATROL_SHIP_BLASTER = 'patrol_ship_blaster';
    public const string PATROL_SHIP_TELSATRON = 'patrol_ship_telsatron';
    public const string DOOR_PISTON_DETECTOR = 'door_piston_detector';

    // Equipment skin from player
    public const string SOFA_PLAYER_SKIN_SLOT = 'sofa_player_skin_slot';
    public const string SOFA_BROWN = 'sofa_brown';
    public const string SOFA_GREEN = 'sofa_green';
    public const string SOFA_PINK = 'sofa_pink';
    public const string SOFA_WHITE = 'sofa_white';
    public const string PATROL_SHIP_PLAYER_SKIN_SLOT = 'patrol_ship_player_skin_slot';
    public const string PATROL_SHIP_GOLD = 'patrol_ship_gold';
    public const string PATROL_SHIP_RED_BLACK = 'patrol_ship_red_black';
    public const string ICARUS_PLAYER_SKIN_SLOT = 'icarus_skin_slot';
    public const string ICARUS_RED_BLACK = 'icarus_red_black';

    // Room skins from projects and research
    public const string MAGNETIC_NET = 'magnetic_net';
    public const string MAGNETIC_RETURN = 'magnetic_return';
    public const string QUANTUM_SENSOR = 'quantum_sensor';
    public const string TAKEOFF_PLATFORM_PROPULSION = 'takeoff_platform_propulsion';
    public const string PNEUMATIC_DISTRIBUTOR = 'pneumatic_distributor';
    public const string PILGRED_ACTIVE = 'pilgred_active';
    public const string REACTOR_BROKEN = 'reactor_broken';
    public const string BAY_DOOR_EXTRALARGE = 'bay_door_extralarge';
    public const string GARDEN_INCUBATOR = 'garden_incubator';
    public const string GARDEN_LAMP = 'garden_lamp';
    public const string KITCHEN_APERO_TABLE = 'kitchen_apero_table';
    public const string PLASMA_SHIELD_ACTIVE = 'plasma_shield_active';
    public const string AUTO_WATERING = 'auto_watering';
    public const string NOISE_REDUCER = 'noise_reducer';
    public const string ICARUS_LAVATORY = 'icarus_lavatory';
    public const string ANTISPORE_GAZ = 'antispore_gaz';
    public const string ANABOLICS = 'anabolics';
    public const string CONSTIPASPORE_SERUM = 'constipaspore_serum';
    public const string MERIDON_SCRAMBLER = 'meridon_scrambler';
    public const string MUSH_HUNTER_ZC16H = 'mush_hunter_zc16h';
    public const string MUSHOVORE_BACTERIA = 'mushovere_bacteria';
    public const string MUSH_LANGUAGE = 'mush_language';
    public const string MUSH_RACES = 'mush_races';
    public const string MUSH_REPRODUCTIVE_SYSTEM = 'mush_reproductive_system';
    public const string NATAMY_RIFLE = 'natamy_rifle';
    public const string NCC_CONTACT_LENSES = 'ncc_contact_lenses';
    public const string PATULINE_SCRAMBLER = 'patuline_scrambler';
    public const string PERPETUAL_HYDRATION = 'perpetual_hydration';
    public const string PHEROMODEM = 'pheromodem';
    public const string SPORE_SUCKER = 'spore_sucker';
    public const string RETRO_FUNGAL_SERUM = 'retro_fungal_serum';
    public const string ULTRA_HEALING_POMADE = 'ultra_healing_pomade';
    public const string MUSHICIDE_SOAP = 'mushicide_soap';
    public const string DISMANTLING = 'dismantling';
    public const string REPAIR_PATROL_SHIP = 'repair_patrol_ship';
    public const string OXYGENATED_DUCTS = 'oxygenated_ducts';

    // Room skins from player
    public const string ALPHA_POSTER = 'alpha_poster';

    public const string ALL_ROOM = 'all_room';

    public static function getProjectEquipmentsSkins(): array
    {
        return [
            EquipmentEnum::KITCHEN => [
                ProjectName::APERO_KITCHEN->value => self::KITCHEN_APERO,
            ],
            EquipmentEnum::ANTENNA => [
                ProjectName::RADAR_TRANS_VOID->value => self::ANTENNA_SPATIAL_WAVE,
            ],
            EquipmentEnum::TURRET_COMMAND => [
                ProjectName::TESLA_SUP2X->value => self::TURRET_TESLA,
                ProjectName::TURRET_EXTRA_FIRE_RATE->value => self::TURRET_CHARGES,
            ],
            EquipmentEnum::COFFEE_MACHINE => [
                ProjectName::FISSION_COFFEE_ROASTER->value => self::COFFEE_MACHINE_FISSION,
                ProjectName::GUARANA_CAPPUCCINO->value => self::COFFEE_MACHINE_GUARANA,
            ],
            EquipmentEnum::ICARUS => [
                ProjectName::ICARUS_LARGER_BAY->value => self::ICARUS_LARGE,
                ProjectName::ICARUS_ANTIGRAV_PROPELLER->value => self::ICARUS_THRUSTER,
            ],
            EquipmentEnum::AUXILIARY_TERMINAL => [
                ProjectName::NERON_PROJECT_THREAD->value => self::NERON_PARTICIPATION_AUXILIARY_TERMINAL,
            ],
            EquipmentEnum::NERON_CORE => [
                ProjectName::NERON_PROJECT_THREAD->value => self::NERON_CORE_PARTICIPATION,
            ],
            EquipmentEnum::PATROL_SHIP => [
                ProjectName::PATROLSHIP_EXTRA_AMMO->value => self::PATROL_SHIP_TELSATRON,
                ProjectName::PATROLSHIP_BLASTER_GUN->value => self::PATROL_SHIP_BLASTER,
            ],
            EquipmentEnum::DOOR => [
                ProjectName::DOOR_SENSOR->value => self::DOOR_PISTON_DETECTOR,
            ],
        ];
    }

    public static function getProjectPlacesSkins(): array
    {
        return [
            self::ALL_ROOM => [
                ProjectName::AUTO_WATERING->value => self::AUTO_WATERING,
                ProjectName::ANTISPORE_GAS->value => self::ANTISPORE_GAZ,
                ProjectName::DISMANTLING->value => self::DISMANTLING,
            ],
            RoomEnum::BRIDGE => [
                ProjectName::CHIPSET_ACCELERATION->value => self::QUANTUM_SENSOR,
            ],
            RoomEnum::ALPHA_BAY => [
                ProjectName::MAGNETIC_NET->value => self::MAGNETIC_NET,
                ProjectName::PATROL_SHIP_LAUNCHER->value => self::TAKEOFF_PLATFORM_PROPULSION,
                ProjectName::BAY_DOOR_XXL->value => self::BAY_DOOR_EXTRALARGE,
                ProjectName::NOISE_REDUCER->value => self::NOISE_REDUCER,
            ],
            RoomEnum::BRAVO_BAY => [
                ProjectName::MAGNETIC_NET->value => self::MAGNETIC_NET,
                ProjectName::PATROL_SHIP_LAUNCHER->value => self::TAKEOFF_PLATFORM_PROPULSION,
                ProjectName::NOISE_REDUCER->value => self::NOISE_REDUCER,
            ],
            RoomEnum::ALPHA_BAY_2 => [
                ProjectName::MAGNETIC_NET->value => self::MAGNETIC_NET,
                ProjectName::PATROL_SHIP_LAUNCHER->value => self::TAKEOFF_PLATFORM_PROPULSION,
                ProjectName::BAY_DOOR_XXL->value => self::BAY_DOOR_EXTRALARGE,
                ProjectName::CALL_OF_DIRTY->value => self::REPAIR_PATROL_SHIP,
                ProjectName::NOISE_REDUCER->value => self::NOISE_REDUCER,
            ],
            RoomEnum::ICARUS_BAY => [
                ProjectName::ICARUS_LAVATORY->value => self::ICARUS_LAVATORY,
                ProjectName::AUTO_RETURN_ICARUS->value => self::MAGNETIC_RETURN,
                ProjectName::NOISE_REDUCER->value => self::NOISE_REDUCER,
            ],
            RoomEnum::LABORATORY => [
                ProjectName::ANABOLICS->value => self::ANABOLICS,
                ProjectName::CONSTIPASPORE_SERUM->value => self::CONSTIPASPORE_SERUM,
                ProjectName::MERIDON_SCRAMBLER->value => self::MERIDON_SCRAMBLER,
                ProjectName::MUSH_HUNTER_ZC16H->value => self::MUSH_HUNTER_ZC16H,
                ProjectName::MUSHOVORE_BACTERIA->value => self::MUSHOVORE_BACTERIA,
                ProjectName::MUSH_LANGUAGE->value => self::MUSH_LANGUAGE,
                ProjectName::MUSH_RACES->value => self::MUSH_RACES,
                ProjectName::MUSH_REPRODUCTIVE_SYSTEM->value => self::MUSH_REPRODUCTIVE_SYSTEM,
                ProjectName::NATAMY_RIFLE->value => self::NATAMY_RIFLE,
                ProjectName::NCC_CONTACT_LENSES->value => self::NCC_CONTACT_LENSES,
                ProjectName::PATULINE_SCRAMBLER->value => self::PATULINE_SCRAMBLER,
                ProjectName::PERPETUAL_HYDRATION->value => self::PERPETUAL_HYDRATION,
                ProjectName::SPORE_SUCKER->value => self::SPORE_SUCKER,
                ProjectName::RETRO_FUNGAL_SERUM->value => self::RETRO_FUNGAL_SERUM,
            ],
            RoomEnum::MEDLAB => [
                ProjectName::ULTRA_HEALING_POMADE->value => self::ULTRA_HEALING_POMADE,
            ],
            RoomEnum::REFECTORY => [
                ProjectName::APERO_KITCHEN->value => self::KITCHEN_APERO_TABLE,
                ProjectName::FOOD_RETAILER->value => self::PNEUMATIC_DISTRIBUTOR,
            ],
            RoomEnum::HYDROPONIC_GARDEN => [
                ProjectName::HEAT_LAMP->value => self::GARDEN_LAMP,
                ProjectName::HYDROPONIC_INCUBATOR->value => self::GARDEN_INCUBATOR,
                ProjectName::FOOD_RETAILER->value => self::PNEUMATIC_DISTRIBUTOR,
            ],
            RoomEnum::ENGINE_ROOM => [
                ProjectName::PILGRED->value => self::PILGRED_ACTIVE,
                ProjectName::PLASMA_SHIELD->value => self::PLASMA_SHIELD_ACTIVE,
            ],
            RoomEnum::ALPHA_DORM => [
                ProjectName::MUSHICIDE_SOAP->value => self::MUSHICIDE_SOAP,
                ProjectName::OXY_MORE->value => self::OXYGENATED_DUCTS,
            ],
            RoomEnum::BRAVO_DORM => [
                ProjectName::MUSHICIDE_SOAP->value => self::MUSHICIDE_SOAP,
            ],
        ];
    }
}
