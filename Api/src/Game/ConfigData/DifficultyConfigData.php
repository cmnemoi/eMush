<?php

namespace Mush\Game\ConfigData;

use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\DifficultyEnum;

/** @codeCoverageIgnore */
class DifficultyConfigData
{
    public static $dataArray = [
        [
            'name' => 'default',
            'equipmentBreakRate' => 30,
            'doorBreakRate' => 40,
            'equipmentFireBreakRate' => 30,
            'startingFireRate' => 2,
            'maximumAllowedSpreadingFires' => 2,
            'propagatingFireRate' => 30,
            'hullFireDamageRate' => 20,
            'tremorRate' => 5,
            'electricArcRate' => 5,
            'metalPlateRate' => 5,
            'panicCrisisRate' => 5,
            'firePlayerDamage' => [2 => 1],
            'fireHullDamage' => [2 => 1, 4 => 1],
            'electricArcPlayerDamage' => [3 => 1],
            'tremorPlayerDamage' => [1 => 1, 2 => 1, 3 => 1],
            'metalPlatePlayerDamage' => [4 => 1, 5 => 1, 6 => 1],
            'panicCrisisPlayerDamage' => [3 => 1],
            'plantDiseaseRate' => 5,
            'cycleDiseaseRate' => 20,
            'equipmentBreakRateDistribution' => [
                EquipmentEnum::BIOS_TERMINAL => 3,
                EquipmentEnum::COMMUNICATION_CENTER => 6,
                EquipmentEnum::NERON_CORE => 6,
                EquipmentEnum::RESEARCH_LABORATORY => 6,
                EquipmentEnum::CALCULATOR => 6,
                EquipmentEnum::EMERGENCY_REACTOR => 6,
                EquipmentEnum::REACTOR_LATERAL => 6,
                EquipmentEnum::REACTOR_LATERAL_ALPHA => 6,
                EquipmentEnum::REACTOR_LATERAL_BRAVO => 6,
                EquipmentEnum::GRAVITY_SIMULATOR => 6,
                EquipmentEnum::ASTRO_TERMINAL => 12,
                EquipmentEnum::COMMAND_TERMINAL => 12,
                EquipmentEnum::PLANET_SCANNER => 12,
                EquipmentEnum::JUKEBOX => 12,
                EquipmentEnum::ANTENNA => 12,
                EquipmentEnum::PATROL_SHIP_ALPHA_JUJUBE => 12,
                EquipmentEnum::PATROL_SHIP_ALPHA_LONGANE => 12,
                EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN => 12,
                EquipmentEnum::PATROL_SHIP_ALPHA_2_WALLIS => 12,
                EquipmentEnum::PATROL_SHIP_BRAVO_EPICURE => 12,
                EquipmentEnum::PATROL_SHIP_BRAVO_PLANTON => 12,
                EquipmentEnum::PATROL_SHIP_BRAVO_SOCRATE => 12,
                EquipmentEnum::PASIPHAE => 12,
                EquipmentEnum::COMBUSTION_CHAMBER => 12,
                EquipmentEnum::KITCHEN => 12,
                EquipmentEnum::DYNARCADE => 12,
                EquipmentEnum::COFFEE_MACHINE => 12,
                EquipmentEnum::MYCOSCAN => 12,
                EquipmentEnum::TURRET_COMMAND => 12,
                EquipmentEnum::SURGERY_PLOT => 12,
                EquipmentEnum::THALASSO => 25,
                EquipmentEnum::CAMERA_EQUIPMENT => 25,
                EquipmentEnum::SHOWER => 25,
                EquipmentEnum::FUEL_TANK => 25,
                EquipmentEnum::OXYGEN_TANK => 25,
            ],
            'difficultyModes' => [
                DifficultyEnum::NORMAL => 1,
                DifficultyEnum::HARD => 5,
                DifficultyEnum::VERY_HARD => 10,
            ],
            'hunterSpawnRate' => 20,
            'hunterSafeCycles' => [2, 3],
            'startingHuntersNumberOfTruceCycles' => 2,
        ],
    ];
}
