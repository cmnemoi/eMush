<?php

namespace Mush\Hunter\ConfigData;

use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterTargetEnum;
use Mush\Status\Enum\HunterStatusEnum;

/** @codeCoverageIgnore */
class HunterConfigData
{
    public static array $dataArray = [
        [
            'name' => HunterEnum::ASTEROID . '_default',
            'hunterName' => HunterEnum::ASTEROID,
            'initialHealth' => 20,
            'initialStatuses' => [HunterStatusEnum::ASTEROID_TRUCE_CYCLES . '_default'],
            'damageRange' => [
                0 => 1,
            ],
            'hitChance' => 100,
            'dodgeChance' => 20,
            'drawCost' => 25,
            'maxPerWave' => 2,
            'drawWeight' => 1,
            'spawnDifficulty' => 5,
            'scrapDropTable' => [
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
                3 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 0,
                HunterTargetEnum::PLAYER => 0,
                HunterTargetEnum::TRANSPORT => 0,
            ],
            'bonusAfterFailedShot' => 0,
            'numberOfActionsPerCycle' => 1,
        ],
        [
            'name' => HunterEnum::DICE . '_default',
            'hunterName' => HunterEnum::DICE,
            'initialHealth' => 30,
            'initialStatuses' => [],
            'damageRange' => [
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
            ],
            'hitChance' => 60,
            'dodgeChance' => 20,
            'drawCost' => 30,
            'maxPerWave' => 1,
            'drawWeight' => 1,
            'spawnDifficulty' => 10,
            'scrapDropTable' => [
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 2,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
                3 => 1,
                4 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 15,
                HunterTargetEnum::PLAYER => 5,
                HunterTargetEnum::TRANSPORT => 20,
            ],
            'bonusAfterFailedShot' => 10,
            'numberOfActionsPerCycle' => 3,
        ],
        [
            'name' => HunterEnum::HUNTER . '_default',
            'hunterName' => HunterEnum::HUNTER,
            'initialHealth' => 6,
            'initialStatuses' => [],
            'damageRange' => [
                2 => 1,
                3 => 1,
                4 => 1,
            ],
            'hitChance' => 80,
            'dodgeChance' => 50,
            'drawCost' => 10,
            'maxPerWave' => null,
            'drawWeight' => 10,
            'spawnDifficulty' => 1,
            'scrapDropTable' => [
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 1,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 15,
                HunterTargetEnum::PLAYER => 5,
                HunterTargetEnum::TRANSPORT => 20,
            ],
            'bonusAfterFailedShot' => 10,
            'numberOfActionsPerCycle' => 1,
        ],
        [
            'name' => HunterEnum::SPIDER . '_default',
            'hunterName' => HunterEnum::SPIDER,
            'initialHealth' => 6,
            'initialStatuses' => [],
            'damageRange' => [
                1 => 1,
                2 => 1,
                3 => 1,
            ],
            'hitChance' => 40,
            'dodgeChance' => 60,
            'drawCost' => 20,
            'maxPerWave' => 2,
            'drawWeight' => 1,
            'spawnDifficulty' => 5,
            'scrapDropTable' => [
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 2,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 15,
                HunterTargetEnum::PLAYER => 5,
                HunterTargetEnum::TRANSPORT => 20,
            ],
            'bonusAfterFailedShot' => 10,
            'numberOfActionsPerCycle' => 1,
        ],
        [
            'name' => HunterEnum::TRAX . '_default',
            'hunterName' => HunterEnum::TRAX,
            'initialHealth' => 10,
            'initialStatuses' => [],
            'damageRange' => [
                2 => 1,
                3 => 1,
            ],
            'hitChance' => 50,
            'dodgeChance' => 50,
            'drawCost' => 20,
            'maxPerWave' => 2,
            'drawWeight' => 2,
            'spawnDifficulty' => 5,
            'scrapDropTable' => [
                ItemEnum::METAL_SCRAPS => 2,
                ToolItemEnum::SPACE_CAPSULE => 1,
                ItemEnum::PLASTIC_SCRAPS => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
                3 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 15,
                HunterTargetEnum::PLAYER => 5,
                HunterTargetEnum::TRANSPORT => 20,
            ],
            'bonusAfterFailedShot' => 10,
            'numberOfActionsPerCycle' => 1,
        ],
        [
            'name' => HunterEnum::TRANSPORT . '_default',
            'hunterName' => HunterEnum::TRANSPORT,
            'initialHealth' => 16,
            'initialStatuses' => [],
            'damageRange' => [0 => 1],
            'hitChance' => 0,
            'dodgeChance' => 0,
            'drawCost' => 0,
            'maxPerWave' => 0,
            'drawWeight' => 0,
            'spawnDifficulty' => 0,
            'scrapDropTable' => [
                GameRationEnum::STANDARD_RATION => 35,
                ItemEnum::METAL_SCRAPS => 20,
                ToolItemEnum::SPACE_CAPSULE => 15,
                ItemEnum::BLASTER => 10,
                ItemEnum::GRENADE => 10,
                ItemEnum::LUNCHBOX => 10,
                ItemEnum::PLASTIC_SCRAPS => 10,
                ToolItemEnum::BANDAGE => 10,
                ItemEnum::HYDROPOT => 1,
            ],
            'numberOfDroppedScrap' => [
                1 => 2,
                2 => 1,
                3 => 1,
                4 => 1,
            ],
            'targetProbabilities' => [
                HunterTargetEnum::PATROL_SHIP => 0,
                HunterTargetEnum::PLAYER => 0,
            ],
            'bonusAfterFailedShot' => 0,
            'numberOfActionsPerCycle' => 0,
        ],
    ];

    public static function getByName(string $name): array
    {
        foreach (self::$dataArray as $data) {
            if ($data['hunterName'] === $name) {
                return $data;
            }
        }

        throw new \Exception("Hunter config not found: {$name}");
    }
}
