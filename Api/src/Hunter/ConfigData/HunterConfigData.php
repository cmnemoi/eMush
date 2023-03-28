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
            'minDamage' => 0,
            'maxDamage' => 0,
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
            'minDamage' => 3,
            'maxDamage' => 6,
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
            'minDamage' => 2,
            'maxDamage' => 4,
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
            'minDamage' => 1,
            'maxDamage' => 3,
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
            'minDamage' => 2,
            'maxDamage' => 3,
            'hitChance' => 50,
            'dodgeChance' => 50,
            'drawCost' => 20,
            'maxPerWave' => 2,
            'drawWeight' => 2,
        ],
    ];
}
