<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Room\Entity\Room;
use Mush\Room\Enum\RoomEnum;

class RoomFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(Room::class, [
            'name' => RoomEnum::BRIDGE,
        ]);
    }
}
