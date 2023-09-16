<?php

namespace Mush\Tests\unit\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\EventCreationService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
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

    public function testCreatePlayerVariableEvents()
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

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setQuantity(1)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
        ;

        // range is a player
        $events = $this->service->createEvents($eventConfig, $player1, $player1, [], new \DateTime());

        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player1->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player1, $event->getPlayer());

        // range is a place
        $events = $this->service->createEvents($eventConfig, $place1, $player1, [], new \DateTime());

        $this->assertCount(2, $events);
        $event = $events[0];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player1->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player1, $event->getPlayer());
        $event = $events[1];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player2->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player2, $event->getPlayer());

        // range is Daedalus
        $events = $this->service->createEvents($eventConfig, $daedalus, $player1, [], new \DateTime());

        $this->assertCount(3, $events);
        $event = $events[0];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player1->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player1, $event->getPlayer());
        $event = $events[1];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player2->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player2, $event->getPlayer());
        $event = $events[2];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player3->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($player3, $event->getPlayer());
    }

    public function testCreatePlayerVariableEventsReverted()
    {
        // range is a player
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

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            $characterConfig
        );

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setQuantity(1)
            ->setTargetVariable(PlayerVariableEnum::MOVEMENT_POINT)
            ->setVariableHolderClass(ModifierHolderClassEnum::PLAYER)
        ;

        // range is a player
        $events = $this->service->createEvents($eventConfig, $player1, $player1, [], new \DateTime(), true);

        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(PlayerVariableEvent::class, $event);
        $this->assertEquals($player1->getVariableByName(PlayerVariableEnum::MOVEMENT_POINT), $event->getVariable());
        $this->assertEquals(-1, $event->getQuantity());
        $this->assertEquals($player1, $event->getPlayer());
    }

    public function testCreateDaedalusVariableEvents()
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

        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setEventName('eventName')
            ->setQuantity(1)
            ->setTargetVariable(DaedalusVariableEnum::SPORE)
            ->setVariableHolderClass(ModifierHolderClassEnum::DAEDALUS)
        ;

        // range is a player
        $events = $this->service->createEvents($eventConfig, $player1, $player1, [], new \DateTime());

        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(DaedalusVariableEvent::class, $event);
        $this->assertEquals($daedalus->getVariableByName(DaedalusVariableEnum::SPORE), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($daedalus, $event->getDaedalus());

        // range is a place
        $events = $this->service->createEvents($eventConfig, $place1, $player1, [], new \DateTime());
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(DaedalusVariableEvent::class, $event);
        $this->assertEquals($daedalus->getVariableByName(DaedalusVariableEnum::SPORE), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($daedalus, $event->getDaedalus());

        // range is Daedalus
        $events = $this->service->createEvents($eventConfig, $daedalus, $player1, [], new \DateTime());
        $this->assertCount(1, $events);
        $event = $events[0];
        $this->assertInstanceOf(DaedalusVariableEvent::class, $event);
        $this->assertEquals($daedalus->getVariableByName(DaedalusVariableEnum::SPORE), $event->getVariable());
        $this->assertEquals(1, $event->getQuantity());
        $this->assertEquals($daedalus, $event->getDaedalus());
    }
}
