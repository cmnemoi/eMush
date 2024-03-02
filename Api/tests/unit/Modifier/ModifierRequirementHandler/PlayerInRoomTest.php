<?php

namespace Mush\Tests\unit\Modifier\ModifierRequirementHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Modifier\ModifierRequirementHandler\RequirementPlayerInRoom;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class PlayerInRoomTest extends TestCase
{
    private RequirementPlayerInRoom $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new RequirementPlayerInRoom();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testPlayerInRoomActivationRequirementModifier()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $room->setDaedalus($daedalus);
        $player1 = new Player();
        $player1->setPlace($room);

        $playerInfo = new PlayerInfo($player1, new User(), new CharacterConfig());

        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::PLAYER_IN_ROOM);
        $modifierActivationRequirement->setActivationRequirement(ModifierRequirementEnum::NOT_ALONE);

        $result = $this->service->checkRequirement($modifierActivationRequirement, $player1);
        $this->assertFalse($result);

        $player2 = new Player();
        $player2->setPlace($room);
        $playerInfo = new PlayerInfo($player2, new User(), new CharacterConfig());

        $result = $this->service->checkRequirement($modifierActivationRequirement, $player1);
        $this->assertTrue($result);
    }
}
