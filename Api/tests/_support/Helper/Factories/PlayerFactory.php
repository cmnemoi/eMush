<?php

namespace Mush\Tests\Helper\Factories;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\Module;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\User\Entity\User;

class PlayerFactory extends Module
{
    public function _beforeSuite($settings = [])
    {
        $factory = $this->getModule('DataFactory');

        $factory->_define(User::class, [
            'user_id' => 'test_user',
            'username' => 'username',
        ]);

        $factory->_define(Player::class, [
            'triumph' => 0,
        ]);
        $factory->_define(CharacterConfig::class, [
            'name' => CharacterEnum::GIOELE . '_' . GameConfigEnum::TEST,
            'characterName' => CharacterEnum::GIOELE,
            'maxNumberPrivateChannel' => 3,
            'initHealthPoint' => 10,
            'maxHealthPoint' => 12,
            'initMoralPoint' => 10,
            'maxMoralPoint' => 12,
            'initSatiety' => 0,
            'initActionPoint' => 10,
            'maxActionPoint' => 12,
            'initMovementPoint' => 10,
            'maxMovementPoint' => 12,
            'maxItemInInventory' => 12,
        ]);
    }
}
