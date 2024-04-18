<?php

namespace Mush\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;

class RoomFactory extends Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(Place::class, [
            'name' => RoomEnum::BRIDGE . '_' . GameConfigEnum::TEST,
            'placeName' => RoomEnum::BRIDGE,
        ]);
    }
}
