<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Daedalus\Entity\Daedalus;

class DaedalusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(Daedalus::class, [
            'day' => 1,
            'cycle' => 1,
            'cycleStartedAt' => new \DateTime('today midnight'),
        ]);
    }
}
