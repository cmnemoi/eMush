<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Game\Entity\VariableEventConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Enum\EventTargetNameEnum;
use Mush\Modifier\Service\EventCreationService;
use Mush\Modifier\Service\ModifierRequirementServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EventCreationServiceTest extends TestCase
{
    private EventCreationService $service;

    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $equipmentRepository;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    /** @var Mockery\Mock|ModifierRequirementServiceInterface */
    private ModifierRequirementServiceInterface $modifierRequirementService;

    /**
     * @before
     */
    public function before()
    {
        $this->equipmentRepository = \Mockery::mock(GameEquipmentRepository::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->modifierRequirementService = \Mockery::mock(ModifierRequirementServiceInterface::class);

        $this->service = new EventCreationService(
            $this->equipmentRepository,
            $this->randomService,
            $this->modifierRequirementService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testGetPlayersTargetPlayer()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

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
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::PLAYER);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($eventRequirement, $player1)
            ->andReturn(true)
            ->once();
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $player1,
            author: $player1
        );

        self::assertCount(1, $eventTargets);
        $player = $eventTargets[0];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player1, $player);
    }

    public function testGetPlayersTargetPlace()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

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

        // range is a place
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::PLAYER);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->andReturn(true)
            ->twice();
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $place1,
            author: $player1
        );

        self::assertCount(2, $eventTargets);
        $player = $eventTargets[0];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player1, $player);

        $player = $eventTargets[1];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player2, $player);
    }

    public function testGetPlayersTargetDaedalus()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

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

        // range is Daedalus
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::PLAYER);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->andReturn(true)
            ->times(3);
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $daedalus,
            author: $player1
        );

        self::assertCount(3, $eventTargets);
        $player = $eventTargets[0];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player1, $player);
        $player = $eventTargets[1];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player2, $player);
        $player = $eventTargets[2];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player3, $player);
    }

    public function testGetPlayersTargetPlaceRandom()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

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

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2);
        $daedalus->addPlace($place1);

        // range is a place
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setVariableHolderClass(EventTargetNameEnum::PLAYER);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->andReturn(true)
            ->once();
        $this->randomService
            ->shouldReceive('getRandomElements')
            ->with([$player1, $player2], 1)
            ->andReturn([$player1])
            ->once();

        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [EventTargetNameEnum::SINGLE_RANDOM],
            range: $place1,
            author: $player1
        );

        self::assertCount(1, $eventTargets);
        $player = $eventTargets[0];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player1, $player);
    }

    public function testGetPlayersTargetPlaceExcludeHolder()
    {
        $characterConfig = new CharacterConfig();
        $characterConfig
            ->setName('character name')
            ->setMaxHealthPoint(16)
            ->setInitActionPoint(10)
            ->setInitMovementPoint(10)
            ->setInitMoralPoint(10);

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

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2);
        $daedalus->addPlace($place1);

        // range is a place
        $eventConfig = new VariableEventConfig();
        $eventConfig
            ->setVariableHolderClass(EventTargetNameEnum::PLAYER);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($eventRequirement, $player2)
            ->andReturn(true)
            ->once();
        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [EventTargetNameEnum::EXCLUDE_PROVIDER],
            range: $place1,
            author: $player1
        );

        self::assertCount(1, $eventTargets);
        $player = current($eventTargets);
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player2, $player);
    }

    public function testGetDaedalusTargetFromPlayer()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setName('character name')
            ->setDailySporeNb(2)
            ->setInitHull(1)
            ->setInitShield(1)
            ->setInitOxygen(1)
            ->setInitFuel(2);

        $player1 = new Player();

        $place1 = new Place();
        $place1->addPlayer($player1);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlace($place1)->setDaedalusVariables($daedalusConfig);

        // range is a player
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::DAEDALUS);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($eventRequirement, $daedalus)
            ->andReturn(true)
            ->once();

        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $player1,
            author: $player1
        );

        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }

    public function testGetDaedalusTargetFromPlace()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setName('character name')
            ->setDailySporeNb(2)
            ->setInitHull(1)
            ->setInitShield(1)
            ->setInitOxygen(1)
            ->setInitFuel(2);

        $player1 = new Player();

        $place1 = new Place();
        $place1->addPlayer($player1);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlace($place1)->setDaedalusVariables($daedalusConfig);

        // range is a place
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::DAEDALUS);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($eventRequirement, $daedalus)
            ->andReturn(true)
            ->once();

        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $place1,
            author: $player1
        );
        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }

    public function testGetDaedalusTargetFromDaedalus()
    {
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setName('character name')
            ->setDailySporeNb(2)
            ->setInitHull(1)
            ->setInitShield(1)
            ->setInitOxygen(1)
            ->setInitFuel(2);

        $player1 = new Player();

        $place1 = new Place();
        $place1->addPlayer($player1);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlayer($player1)
            ->addPlace($place1)
            ->setDaedalusVariables($daedalusConfig);

        // range is Daedalus
        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::DAEDALUS);

        $eventRequirement = new ArrayCollection();
        $variableModifierConfig = new TriggerEventModifierConfig('test_trigger_event');
        $variableModifierConfig->setModifierActivationRequirements($eventRequirement);

        $this->modifierRequirementService
            ->shouldReceive('checkRequirements')
            ->with($eventRequirement, $daedalus)
            ->andReturn(true)
            ->once();

        $eventTargets = $this->service->getEventTargetsFromModifierHolder(
            eventConfig: $eventConfig,
            eventTargetRequirements: $eventRequirement,
            targetFilters: [],
            range: $daedalus,
            author: $player1
        );
        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }
}
