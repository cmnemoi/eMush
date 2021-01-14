<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;

class DaedalusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(DifficultyConfig::class, [
            ]);

        $factory->_define(GameConfig::class, [
            'name' => 'default',
            'difficultyConfig' => 'entity|' . DifficultyConfig::class,
            'nbMush' => 2,
            'cycleLength' => 3,
            'timeZone' => 'Paris/Europe',
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
            'maxOxygen' => 32,
            'maxFuel' => 32,
        ]);

        $factory->_define(Daedalus::class, [
            'gameConfig' => 'entity|' . GameConfig::class,
            'oxygen' => 10,
            'fuel' => 10,
            'hull' => 100,
            'shield' => -2,
            'day' => 1,
            'cycle' => 1,
        ]);
    }
}
