<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;

class EquipmentFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(EquipmentConfig::class, [
            'name' => EquipmentEnum::BED,
        ]);

        $factory->_define(ItemConfig::class, [
            'name' => ItemEnum::COMMANDERS_MANUAL,
        ]);
    }
}
