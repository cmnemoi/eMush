<?php

namespace Mush\Tests\unit\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EventCreationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class EventCreationServiceTest extends TestCase
{
    private EventCreationService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->service = new EventCreationService();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGetPlayersTarget()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10)
        ;

        $player1 = new Player();
        $player1->setPlayerVariables($characterConfig);
        $player2 = new Player();
        $player2->setPlayerVariables($characterConfig);
        $player3 = new Player();
        $player3->setPlayerVariables($characterConfig);

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            $characterConfig
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            $characterConfig
        );
        $playerInfo3 = new PlayerInfo(
            $player3,
            new User(),
            $characterConfig
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);
        $place2 = new Place();
        $place2->addPlayer($player3);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2)->addPlayer($player3);
        $daedalus->addPlace($place1)->addPlace($place2);

        // range is a player
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::PLAYER, $player1);

        $this->assertCount(1, $eventTargets);
        $player = $eventTargets[0];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player1, $player);

        // range is a place
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::PLAYER, $place1);

        $this->assertCount(2, $eventTargets);
        $player = $eventTargets[0];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player1, $player);

        $player = $eventTargets[1];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player2, $player);

        // range is Daedalus
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::PLAYER, $daedalus);

        $this->assertCount(3, $eventTargets);
        $player = $eventTargets[0];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player1, $player);
        $player = $eventTargets[1];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player2, $player);
        $player = $eventTargets[2];
        $this->assertInstanceOf(Player::class, $player);
        $this->assertEquals($player3, $player);
    }

    public function testGetDaedalusTarget()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setName('character name')
            ->setDailySporeNb(2)
            ->setInitHull(1)
            ->setInitShield(1)
            ->setInitOxygen(1)
            ->setInitFuel(2)
        ;

        $player1 = new Player();

        $place1 = new Place();
        $place1->addPlayer($player1);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlace($place1)->setDaedalusVariables($daedalusConfig);

        // range is a player
        $eventTarget = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::DAEDALUS, $player1);

        $this->assertCount(1, $eventTarget);
        $result = $eventTarget[0];
        $this->assertInstanceOf(Daedalus::class, $result);
        $this->assertEquals($daedalus, $result);

        // range is a place
        $eventTarget = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::DAEDALUS, $place1);
        $this->assertCount(1, $eventTarget);
        $result = $eventTarget[0];
        $this->assertInstanceOf(Daedalus::class, $result);
        $this->assertEquals($daedalus, $result);

        // range is Daedalus
        $eventTarget = $this->service->getEventTargetsFromModifierHolder(ModifierHolderClassEnum::DAEDALUS, $daedalus);
        $this->assertCount(1, $eventTarget);
        $result = $eventTarget[0];
        $this->assertInstanceOf(Daedalus::class, $result);
        $this->assertEquals($daedalus, $result);
    }
}
