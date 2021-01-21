<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Status\Entity\Status;

class StatusFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(Status::class, [
        ]);
    }
}
