<?php

declare(strict_types=1);

namespace Mush\Game\ConfigData;

final readonly class DifficultyConfigDto
{
    /**
     * @param array<string, int> $equipmentBreakRateDistribution
     * @param array<string, int> $difficultyModes
     * @param array<int>         $hunterSafeCycles
     * @param array<int, int>    $firePlayerDamage
     * @param array<int, int>    $fireHullDamage
     * @param array<int, int>    $electricArcPlayerDamage
     * @param array<int, int>    $tremorPlayerDamage
     * @param array<int, int>    $metalPlatePlayerDamage
     * @param array<int, int>    $panicCrisisPlayerDamage
     */
    public function __construct(
        public string $name,
        public int $equipmentBreakRate,
        public int $doorBreakRate,
        public int $equipmentFireBreakRate,
        public int $startingFireRate,
        public int $maximumAllowedSpreadingFires,
        public int $propagatingFireRate,
        public int $hullFireDamageRate,
        public int $tremorRate,
        public int $electricArcRate,
        public int $metalPlateRate,
        public int $panicCrisisRate,
        public array $firePlayerDamage,
        public array $fireHullDamage,
        public array $electricArcPlayerDamage,
        public array $tremorPlayerDamage,
        public array $metalPlatePlayerDamage,
        public array $panicCrisisPlayerDamage,
        public int $plantDiseaseRate,
        public int $cycleDiseaseRate,
        public array $equipmentBreakRateDistribution,
        public array $difficultyModes,
        public int $hunterSpawnRate,
        public array $hunterSafeCycles,
        public int $startingHuntersNumberOfTruceCycles,
        public int $linkWithSolCycleFailureRate,
        public int $minTransportSpawnRate,
        public int $maxTransportSpawnRate,
    ) {}
}
