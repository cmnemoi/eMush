<?php

namespace Mush\Hunter\ConfigData;

use Mush\Hunter\Enum\HunterEnum;

/** @codeCoverageIgnore */
class HunterConfigData
{
    public static array $dataArray = [
        [
            'name' => HunterEnum::ASTEROID . '_default',
            'hunterName' => HunterEnum::ASTEROID,
            'initialHealth' => 20,
            'initialCharge' => 6,
            'initialArmor' => 0,
            'damageRange' => [
                6 => 1,
            ],
            'hitChance' => 100,
            'dodgeChance' => 20,
            'drawCost' => 25,
            'maxPerWave' => 2,
            'drawWeight' => 1,
        ],
        [
            'name' => HunterEnum::DICE . '_default',
            'hunterName' => HunterEnum::DICE,
            'initialHealth' => 30,
            'initialCharge' => 0,
            'initialArmor' => 1,
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
        ],
        [
            'name' => HunterEnum::HUNTER . '_default',
            'hunterName' => HunterEnum::HUNTER,
            'initialHealth' => 6,
            'initialCharge' => 0,
            'initialArmor' => 0,
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
        ],
        [
            'name' => HunterEnum::SPIDER . '_default',
            'hunterName' => HunterEnum::SPIDER,
            'initialHealth' => 6,
            'initialCharge' => 0,
            'initialArmor' => 0,
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
        ],
        [
            'name' => HunterEnum::TRAX . '_default',
            'hunterName' => HunterEnum::TRAX,
            'initialHealth' => 10,
            'initialCharge' => 0,
            'initialArmor' => 0,
            'damageRange' => [
                2 => 1,
                3 => 1,
            ],
            'hitChance' => 50,
            'dodgeChance' => 50,
            'drawCost' => 20,
            'maxPerWave' => 2,
            'drawWeight' => 2,
        ],
    ];
}
