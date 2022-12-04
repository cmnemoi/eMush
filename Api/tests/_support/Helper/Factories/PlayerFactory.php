<?php

namespace App\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

class PlayerFactory extends \Codeception\Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(User::class, [
            'user_id' => 'test_user',
            'username' => 'username',
        ]);

        $factory->_define(Player::class, [
            'healthPoint' => 10,
            'moralPoint' => 10,
            'actionPoint' => 10,
            'movementPoint' => 10,
            'triumph' => 0,
            'satiety' => 0,
        ]);
        $factory->_define(CharacterConfig::class, [
            'name' => CharacterEnum::GIOELE,
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
    }
}
