<?php

namespace Mush\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;

class DaedalusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(DifficultyConfig::class, [
            'name' => GameConfigEnum::TEST,
            'equipmentBreakRate' => 0,
            'doorBreakRate' => 0,
            'equipmentFireBreakRate' => 0,
            'startingFireRate' => 0,
            'propagatingFireRate' => 0,
            'hullFireDamageRate' => 0,
            'tremorRate' => 0,
            'metalPlateRate' => 0,
            'electricArcRate' => 0,
            'panicCrisisRate' => 0,
            'fireHullDamage' => [2 => 1],
            'firePlayerDamage' => [2 => 1],
            'electricArcPlayerDamage' => [3 => 1],
            'tremorPlayerDamage' => [2 => 1],
            'metalPlatePlayerDamage' => [6 => 1],
            'panicCrisisPlayerDamage' => [3 => 1],
            'plantDiseaseRate' => 0,
            'cycleDiseaseRate' => 0,
        ]);
        $factory->_define(DaedalusConfig::class, [
            'name' => 'testDaedalusConfig',
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'initOxygen' => 10,
            'initFuel' => 10,
            'initHull' => 100,
            'initShield' => -2,
            'dailySporeNb' => 4,
            'nbMush' => 2,
            'cycleLength' => 3 * 60,
            'cyclePerGameDay' => 8,
        ]);

        $factory->_define(GameConfig::class, [
            'name' => GameConfigEnum::TEST,
            'difficultyConfig' => 'entity|' . DifficultyConfig::class,
            'daedalusConfig' => 'entity|' . DaedalusConfig::class,
        ]);

        $factory->_define(LocalizationConfig::class, [
            'timeZone' => 'UTC',
            'language' => LanguageEnum::FRENCH,
            'name' => 'localization_test',
        ]);

        $factory->_define(Daedalus::class, [
            'day' => 1,
            'cycle' => 1,
            'cycleStartedAt' => new \DateTime('today midnight'),
        ]);
    }
}
