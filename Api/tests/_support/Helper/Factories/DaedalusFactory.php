<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

class DaedalusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(DifficultyConfig::class, [
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
            'maxOxygen' => 32,
            'maxFuel' => 32,
            'maxHull' => 100,
            'initOxygen' => 32,
            'initFuel' => 32,
            'initHull' => 100,
            'initShield' => -2,
            'dailySporeNb' => 4,
        ]);

        $factory->_define(GameConfig::class, [
            'name' => 'default',
            'difficultyConfig' => 'entity|' . DifficultyConfig::class,
            'daedalusConfig' => 'entity|' . DaedalusConfig::class,
            'nbMush' => 2,
            'cycleLength' => 3,
            'cyclePerGameDay' => 8,
            'timeZone' => 'Europe/Paris',
            'language' => 'fr-FR',
            'maxNumberPrivateChannel' => 3,
            'initHealthPoint' => 12,
            'maxHealthPoint' => 12,
            'initMoralPoint' => 12,
            'maxMoralPoint' => 12,
            'initSatiety' => 0,
            'initActionPoint' => 12,
            'maxActionPoint' => 12,
            'initMovementPoint' => 12,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 12,
        ]);

        $factory->_define(Daedalus::class, [
            'gameConfig' => 'entity|' . GameConfig::class,
            'oxygen' => 10,
            'fuel' => 10,
            'hull' => 100,
            'shield' => -2,
            'day' => 1,
            'cycle' => 1,
            'cycleStartedAt' => new \DateTime('today midnight'),
        ]);
    }
}
