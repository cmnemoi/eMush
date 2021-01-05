<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;

class DaedalusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(GameConfig::class, [
            'name' => 'default',
            'timeZone' => 'Paris/Europe',
            'language' => 'fr-FR',
        ]);
        $factory->_define(Daedalus::class, [
            'oxygen' => 10,
            'fuel' => 10,
            'hull' => 100,
            'shield' => -2,
            'day' => 1,
            'cycle' => 1,
        ]);
    }
}
