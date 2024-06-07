<?php

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Player\Entity\Player;

class EquipmentEnum
{
    public const string DOOR = 'door';
    public const string PATROL_SHIP_ALPHA_LONGANE = 'patrol_ship_alpha_longane';
    public const string PATROL_SHIP_ALPHA_JUJUBE = 'patrol_ship_alpha_jujube';
    public const string PATROL_SHIP_ALPHA_TAMARIN = 'patrol_ship_alpha_tamarin';
    public const string PATROL_SHIP_BRAVO_SOCRATE = 'patrol_ship_bravo_socrate';
    public const string PATROL_SHIP_BRAVO_EPICURE = 'patrol_ship_bravo_epicure';
    public const string PATROL_SHIP_BRAVO_PLANTON = 'patrol_ship_bravo_planton';
    public const string PATROL_SHIP_ALPHA_2_WALLIS = 'patrol_ship_alpha_2_wallis';
    public const string PATROL_SHIP = 'patrol_ship';
    public const string PASIPHAE = 'pasiphae';
    public const string ICARUS = 'icarus';
    public const string ANTENNA = 'antenna';
    public const string RADAR_TRANS_VOID_ANTENNA = 'radar_trans_void_antenna';
    public const string CALCULATOR = 'calculator';
    public const string COMMUNICATION_CENTER = 'communication_center';
    public const string COMBUSTION_CHAMBER = 'combustion_chamber';
    public const string NERON_CORE = 'neron_core';
    public const string KITCHEN = 'kitchen';
    public const string NARCOTIC_DISTILLER = 'narcotic_distiller';
    public const string SHOWER = 'shower';
    public const string DYNARCADE = 'dynarcade';
    public const string JUKEBOX = 'jukebox';
    public const string RESEARCH_LABORATORY = 'research_laboratory';
    public const string BED = 'bed';
    public const string MEDLAB_BED = 'medlab_bed';
    public const string SWEDISH_SOFA = 'swedish_sofa';
    public const string COFFEE_MACHINE = 'coffee_machine';
    public const string CRYO_MODULE = 'cryo_module';
    public const string MYCOSCAN = 'mycoscan';
    public const string PILGRED = 'pilgred';
    public const string TURRET_COMMAND = 'turret_command';
    public const string SURGERY_PLOT = 'surgery_plot';
    public const string EMERGENCY_REACTOR = 'emergency_reactor';
    public const string REACTOR_LATERAL = 'reactor_lateral';
    public const string REACTOR_LATERAL_ALPHA = 'reactor_lateral_alpha';
    public const string REACTOR_LATERAL_BRAVO = 'reactor_lateral_bravo';
    public const string FUEL_TANK = 'fuel_tank';
    public const string OXYGEN_TANK = 'oxygen_tank';
    public const string PLANET_SCANNER = 'planet_scanner';
    public const string GRAVITY_SIMULATOR = 'gravity_simulator';
    public const string ASTRO_TERMINAL = 'astro_terminal';
    public const string COMMAND_TERMINAL = 'command_terminal';
    public const string THALASSO = 'thalasso';
    public const string BIOS_TERMINAL = 'bios_terminal';
    public const string AUXILIARY_TERMINAL = 'auxiliary_terminal';
    public const string CAMERA_EQUIPMENT = 'camera_equipment';
    public const string TABULATRIX = 'tabulatrix';
    public const string null = '';

    public static array $terminalSectionTitlesMap = [
        self::COMMAND_TERMINAL => ['orientate_daedalus', 'move_daedalus', 'general_informations', 'pilgred'],
        self::ASTRO_TERMINAL => ['orientation', 'distance'],
        self::BIOS_TERMINAL => ['cpu_priority_name', 'cpu_priority_description', 'crew_lock_name', 'crew_lock_description'],
    ];

    public static array $terminalButtonsMap = [
        self::ASTRO_TERMINAL => ['share_planet'],
    ];

    public static function getBeds(): array
    {
        return [
            self::BED,
            self::SWEDISH_SOFA,
        ];
    }

    public static function getPatrolShips(): ArrayCollection
    {
        return new ArrayCollection([
            self::PATROL_SHIP_ALPHA_LONGANE,
            self::PATROL_SHIP_ALPHA_JUJUBE,
            self::PATROL_SHIP_ALPHA_TAMARIN,
            self::PATROL_SHIP_BRAVO_SOCRATE,
            self::PATROL_SHIP_BRAVO_EPICURE,
            self::PATROL_SHIP_BRAVO_PLANTON,
            self::PATROL_SHIP_ALPHA_2_WALLIS,
            self::PASIPHAE,
        ]);
    }

    public static function getProjectTerminals(): ArrayCollection
    {
        return new ArrayCollection([
            self::NERON_CORE,
            self::AUXILIARY_TERMINAL,
            self::PILGRED,
            self::RESEARCH_LABORATORY,
        ]);
    }

    public static function getTerminals(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTRO_TERMINAL,
            self::AUXILIARY_TERMINAL,
            self::BIOS_TERMINAL,
            ToolItemEnum::BLOCK_OF_POST_IT,
            self::COMMAND_TERMINAL,
            self::NERON_CORE,
            self::PILGRED,
            self::RESEARCH_LABORATORY,
        ]);
    }

    public static function equipmentToNormalizeAsItems(): ArrayCollection
    {
        return new ArrayCollection([self::TABULATRIX]);
    }

    public static function getCriticalItemsGivenPlayer(Player $player): ArrayCollection
    {
        $criticalItems = [ToolItemEnum::HACKER_KIT];

        if ($player->isMush()) {
            return new ArrayCollection($criticalItems);
        }

        $criticalItems = array_merge($criticalItems, [
            ToolItemEnum::EXTINGUISHER,
            GearItemEnum::ANTIGRAV_SCOOTER,
            GearItemEnum::ROLLING_BOULDER,
            GearItemEnum::ADJUSTABLE_WRENCH,
            GearItemEnum::ALIEN_BOTTLE_OPENER,
            GearItemEnum::STAINPROOF_APRON,
            ItemEnum::MUSH_GENOME_DISK,
            ToolItemEnum::MEDIKIT,
            GearItemEnum::SOAP,
            GearItemEnum::SUPER_SOAPER,
            ItemEnum::STARMAP_FRAGMENT,
        ]);

        return new ArrayCollection($criticalItems);
    }
}
