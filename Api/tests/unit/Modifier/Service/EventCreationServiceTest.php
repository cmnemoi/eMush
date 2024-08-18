<?php

namespace Mush\Tests\unit\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
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
        // Give the range is player1
        // Given the target of the event is player
        $player1 = new Player();
        $player2 = new Player();
        $player3 = new Player();

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            new CharacterConfig()
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            new CharacterConfig()
        );
        $playerInfo3 = new PlayerInfo(
            $player3,
            new User(),
            new CharacterConfig()
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);
        $place2 = new Place();
        $place2->addPlayer($player3);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2)->addPlayer($player3);
        $daedalus->addPlace($place1)->addPlace($place2);

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

        // then only player1 should be returned
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
        // Given the range is place1
        // Given player 1 and player 2 are in room
        // Given the target of the event is player
        $player1 = new Player();
        $player2 = new Player();
        $player3 = new Player();

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            new CharacterConfig()
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            new CharacterConfig()
        );
        $playerInfo3 = new PlayerInfo(
            $player3,
            new User(),
            new CharacterConfig()
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);
        $place2 = new Place();
        $place2->addPlayer($player3);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2)->addPlayer($player3);
        $daedalus->addPlace($place1)->addPlace($place2);

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

        // Then player1 and player2 should be returned
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
        // Given the range is daedalus
        // Given player1, player2 and player 3 are in this Daedalus
        // Given the target of the event is player
        $player1 = new Player();
        $player2 = new Player();
        $player3 = new Player();

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            new CharacterConfig()
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            new CharacterConfig()
        );
        $playerInfo3 = new PlayerInfo(
            $player3,
            new User(),
            new CharacterConfig()
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);
        $place2 = new Place();
        $place2->addPlayer($player3);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2)->addPlayer($player3);
        $daedalus->addPlace($place1)->addPlace($place2);

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

        // then all 3 players should be returned
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
        // Given the range is place
        // Given player1 and player2 are in this Place
        // Given the target of the event is player
        // Given the filter is set to 'single_random'
        $player1 = new Player();
        $player2 = new Player();

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            new CharacterConfig()
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            new CharacterConfig()
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2);
        $daedalus->addPlace($place1);

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

        // Then only ONE random player out of player1 and player2 should be returned
        self::assertCount(1, $eventTargets);
        $player = $eventTargets[0];
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player1, $player);
    }

    public function testGetPlayersTargetPlaceExcludeProvider()
    {
        // Given the range is place
        // Given player1 and player2 are in this Place
        // Given the target of the event is player
        // Given the filter is set to 'exclude_provider'
        // Given player1 is the provider of the modifier
        $player1 = new Player();
        $player2 = new Player();

        $playerInfo1 = new PlayerInfo(
            $player1,
            new User(),
            new CharacterConfig()
        );
        $playerInfo2 = new PlayerInfo(
            $player2,
            new User(),
            new CharacterConfig()
        );

        $place1 = new Place();
        $place1->addPlayer($player1)->addPlayer($player2);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlayer($player2);
        $daedalus->addPlace($place1);

        $eventConfig = new VariableEventConfig();
        $eventConfig->setVariableHolderClass(EventTargetNameEnum::PLAYER);

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

        // Then only player2 should be returned
        self::assertCount(1, $eventTargets);
        $player = current($eventTargets);
        self::assertInstanceOf(Player::class, $player);
        self::assertSame($player2, $player);
    }

    public function testGetDaedalusTargetFromPlayer()
    {
        // Given the range is player
        // Given the holder of tha modifier (player1) is in a daedalus
        // Given the target of the event is daedalus
        $player1 = new Player();

        $place1 = new Place();
        $place1->addPlayer($player1);

        $daedalus = new Daedalus();
        $daedalus->addPlayer($player1)->addPlace($place1);

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

        // then the function should return the daedalus of the player
        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }

    public function testGetDaedalusTargetFromPlace()
    {
        // Given the range is place
        // Given the holder of that modifier (place1) is in a daedalus
        // Given the target of the event is daedalus
        $place1 = new Place();

        $daedalus = new Daedalus();
        $daedalus->addPlace($place1);

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
            author: new Player()
        );

        // then the function should return the daedalus of the place
        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }

    public function testGetDaedalusTargetFromDaedalus()
    {
        // Given the range is daedalus
        // Given the target of the event is daedalus
        $daedalus = new Daedalus();

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
            author: new Player()
        );

        // then the function should return the daedalus
        self::assertCount(1, $eventTargets);
        $result = $eventTargets[0];
        self::assertInstanceOf(Daedalus::class, $result);
        self::assertSame($daedalus, $result);
    }
}
