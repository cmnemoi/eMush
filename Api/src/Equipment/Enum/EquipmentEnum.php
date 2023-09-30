<?php

namespace Mush\Equipment\Enum;

use Doctrine\Common\Collections\ArrayCollection;

class EquipmentEnum
{
    public const DOOR = 'door';
    public const PATROL_SHIP_ALPHA_LONGANE = 'patrol_ship_alpha_longane';
    public const PATROL_SHIP_ALPHA_JUJUBE = 'patrol_ship_alpha_jujube';
    public const PATROL_SHIP_ALPHA_TAMARIN = 'patrol_ship_alpha_tamarin';
    public const PATROL_SHIP_BRAVO_SOCRATE = 'patrol_ship_bravo_socrate';
    public const PATROL_SHIP_BRAVO_EPICURE = 'patrol_ship_bravo_epicure';
    public const PATROL_SHIP_BRAVO_PLANTON = 'patrol_ship_bravo_planton';
    public const PATROL_SHIP_ALPHA_2_WALLIS = 'patrol_ship_alpha_2_wallis';
    public const PATROL_SHIP = 'patrol_ship';
    public const PASIPHAE = 'pasiphae';
    public const ICARUS = 'icarus';
    public const ANTENNA = 'antenna';
    public const CALCULATOR = 'calculator';
    public const COMMUNICATION_CENTER = 'communication_center';
    public const COMBUSTION_CHAMBER = 'combustion_chamber';
    public const NERON_CORE = 'neron_core';
    public const KITCHEN = 'kitchen';
    public const NARCOTIC_DISTILLER = 'narcotic_distiller';
    public const SHOWER = 'shower';
    public const SUPPORT_DRONE = 'support_drone';
    public const DYNARCADE = 'dynarcade';
    public const JUKEBOX = 'jukebox';
    public const RESEARCH_LABORATORY = 'research_laboratory';
    public const BED = 'bed';
    public const MEDLAB_BED = 'medlab_bed';
    public const SWEDISH_SOFA = 'swedish_sofa';
    public const COFFEE_MACHINE = 'coffee_machine';
    public const CRYO_MODULE = 'cryo_module';
    public const MYCOSCAN = 'mycoscan';
    public const PILGRED = 'pilgred';
    public const TURRET_COMMAND = 'turret_command';
    public const SURGERY_PLOT = 'surgery_plot';
    public const EMERGENCY_REACTOR = 'emergency_reactor';
    public const REACTOR_LATERAL = 'reactor_lateral';
    public const REACTOR_LATERAL_ALPHA = 'reactor_lateral_alpha';
    public const REACTOR_LATERAL_BRAVO = 'reactor_lateral_bravo';
    public const FUEL_TANK = 'fuel_tank';
    public const OXYGEN_TANK = 'oxygen_tank';
    public const PLANET_SCANNER = 'planet_scanner';
    public const GRAVITY_SIMULATOR = 'gravity_simulator';
    public const ASTRO_TERMINAL = 'astro_terminal';
    public const COMMAND_TERMINAL = 'command_terminal';
    public const THALASSO = 'thalasso';
    public const BIOS_TERMINAL = 'bios_terminal';
    public const AUXILIARY_TERMINAL = 'auxiliary_terminal';
    public const CAMERA_EQUIPMENT = 'camera_equipment';
    public const TABULATRIX = 'tabulatrix';

    public static array $terminalActionParametersMap = [
        self::ASTRO_TERMINAL => [],
        self::AUXILIARY_TERMINAL => [],
        self::BIOS_TERMINAL => [],
        self::COMMAND_TERMINAL => ['orientation'],
        self::COMMUNICATION_CENTER => [],
        self::NERON_CORE => [],
        self::PILGRED => [],
        self::RESEARCH_LABORATORY => [],
    ];

    public static function getBeds(): array
    {
        return [
            self::BED,
            self::MEDLAB_BED,
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

    public static function getTerminals(): ArrayCollection
    {
        return new ArrayCollection([
            self::ASTRO_TERMINAL,
            self::AUXILIARY_TERMINAL,
            self::BIOS_TERMINAL,
            self::COMMAND_TERMINAL,
            self::COMMUNICATION_CENTER,
            self::NERON_CORE,
            self::PILGRED,
            self::RESEARCH_LABORATORY,
        ]);
    }

    public static function equipmentToNormalizeAsItems(): ArrayCollection
    {
        return new ArrayCollection([self::TABULATRIX]);
    }
}
