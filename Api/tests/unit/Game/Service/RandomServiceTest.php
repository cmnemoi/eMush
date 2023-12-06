<?php

namespace Mush\Tests\unit\Game\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Repository\GameEquipmentRepository;
use Mush\Exploration\Repository\PlanetSectorRepository;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\RandomService;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

class RandomServiceTest extends TestCase
{
    /** @var GameEquipmentRepository|Mockery\Mock */
    private GameEquipmentRepository $gameEquipmentRepository;
    /** @var PlanetSectorRepository|Mockery\Mock */
    private PlanetSectorRepository $planetSectorRepository;

    private RandomService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->gameEquipmentRepository = \Mockery::mock(GameEquipmentRepository::class);
        $this->planetSectorRepository = \Mockery::mock(PlanetSectorRepository::class);

        $this->service = new RandomService($this->gameEquipmentRepository, $this->planetSectorRepository);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testRandom()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(-1, $this->service->random(0, 10));
            $this->assertLessThan(11, $this->service->random(0, 10));
        }
        $this->assertIsInt($this->service->random(0, 10));
        $this->assertEquals(10, $this->service->random(10, 10));
    }

    public function testRandomPercent()
    {
        for ($i = 1; $i <= 50; ++$i) {
            $this->assertGreaterThan(0, $this->service->randomPercent());
            $this->assertLessThan(101, $this->service->randomPercent());
        }
        $this->assertIsInt($this->service->randomPercent());
    }

    public function testIsSuccessfull()
    {
        $this->assertIsBool($this->service->isSuccessful(50));
        $this->assertTrue($this->service->isSuccessful(100));
        $this->assertFalse($this->service->isSuccessful(0));
    }

    public function testGetRandomPlayer()
    {
        $playerCollection = new PlayerCollection();

        $playerConfig1 = new CharacterConfig();
        $playerConfig1->setName('player1');
        $player1 = new Player();

        $playerCollection->add($player1);

        $this->assertEquals($player1, $this->service->getRandomPlayer($playerCollection));
    }

    public function testGetPlayerInRoom()
    {
        $room = new Place();
        $player1 = new Player();
        $player1Info = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player1->setPlayerInfo($player1Info);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2->setPlayerInfo($player2Info);

        $room
            ->addPlayer($player1)
            ->addPlayer($player2)
        ;

        $this->assertInstanceOf(Player::class, $this->service->getPlayerInRoom($room));
    }

    public function testGetAlivePlayerInDaedalus()
    {
        $player1 = new Player();
        $player1Info = new PlayerInfo($player1, new User(), new CharacterConfig());
        $player1->setPlayerInfo($player1Info);

        $player2 = new Player();
        $player2Info = new PlayerInfo($player2, new User(), new CharacterConfig());
        $player2Info->setGameStatus(GameStatusEnum::FINISHED);
        $player2->setPlayerInfo($player2Info);

        $daedalus = new Daedalus();
        $daedalus
            ->addPlayer($player2)
            ->addPlayer($player1)
        ;

        for ($i = 1; $i <= 10; ++$i) {
            $this->assertEquals($player1, $this->service->getAlivePlayerInDaedalus($daedalus));
        }
    }

    public function testGetItemInRoom()
    {
        $room = new Place();
        $equipment = new GameEquipment($room);
        $item = new GameItem($room);
        $room
            ->addEquipment($equipment)
            ->addEquipment($item)
        ;

        for ($i = 1; $i <= 10; ++$i) {
            $this->assertInstanceOf(GameItem::class, $this->service->getItemInRoom($room));
            $this->assertEquals($item, $this->service->getItemInRoom($room));
        }
    }

    public function testGetRandomElements()
    {
        $players = ['player1'];
        $this->assertEquals(['player1'], $this->service->getRandomElements($players));

        $players = ['player1', 'player2'];
        $result = $this->service->getRandomElements($players, 2);
        $this->assertContains('player1', $result);
        $this->assertContains('player2', $result);
    }

    public function testGetSingleRandomElementFromProbaArray()
    {
        $players = new ProbaCollection(['player1' => 1]);
        $this->assertEquals('player1', $this->service->getSingleRandomElementFromProbaCollection($players));

        $players = new ProbaCollection(['player1' => 1, 'player2' => 0]);
        $this->assertEquals('player1', $this->service->getSingleRandomElementFromProbaCollection($players));
    }

    public function testGetRandomElementsFromProbaArray()
    {
        $players = new ProbaCollection(['player1' => 1]);
        $this->assertEquals(['player1'], $this->service->getRandomElementsFromProbaCollection($players, 1));

        $players = new ProbaCollection(['player1' => 25, 'player2' => 5, 'player3' => 10, 'player4' => 10, 'player5' => 0]);

        for ($i = 1; $i <= 10; ++$i) {
            $randomPlayer = $this->service->getRandomElementsFromProbaCollection($players, 2);
            $this->assertNotContains('player5', $randomPlayer);
        }
    }

    public function testGetActionOutputWithCritical()
    {
        // critical Fail
        $output = $this->service->outputCriticalChances(100, 100, 0);
        $this->assertEquals($output, ActionOutputEnum::CRITICAL_FAIL);

        // fail
        $output = $this->service->outputCriticalChances(100, 0, 0);
        $this->assertEquals($output, ActionOutputEnum::FAIL);

        // success
        $output = $this->service->outputCriticalChances(0, 0, 0);
        $this->assertEquals($output, ActionOutputEnum::SUCCESS);

        // critical success
        $output = $this->service->outputCriticalChances(0, 0, 100);
        $this->assertEquals($output, ActionOutputEnum::CRITICAL_SUCCESS);
    }

    public function testGetRandomDaedalusEquipmentFromProbaArray()
    {
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setEquipmentBreakRateDistribution(['equipment' => 1]);
        $gameConfig = new GameConfig();
        $gameConfig->setDifficultyConfig($difficultyConfig);

        $daedalus = new Daedalus();

        $room = new Place();
        $daedalus->setPlaces(new ArrayCollection([$room]));
        $equipment = new GameEquipment($room);

        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $this->gameEquipmentRepository
            ->shouldReceive('findByNameAndDaedalus')
            ->withArgs(['equipment', $daedalus])
            ->andReturn([$equipment])
        ;

        $draw = $this->service->getRandomDaedalusEquipmentFromProbaCollection(
            new ProbaCollection(['equipment' => 1]),
            1,
            $daedalus
        )[0];

        $this->assertEquals(
            $equipment,
            $draw
        );
    }

    public function testGetSingleRandomElementFromProbaCollectionReturnsCorrectRepresentation()
    {
        // given an equiprobable collection of 4 items
        $items = new ProbaCollection([
            ItemEnum::FUEL_CAPSULE => 1,
            ItemEnum::OXYGEN_CAPSULE => 1,
            ItemEnum::METAL_SCRAPS => 1,
            ItemEnum::PLASTIC_SCRAPS => 1,
        ]);

        // when we draw 100 000 times one item
        $n = 100000;
        $p = 1 / 4;
        $sigma = sqrt($n * $p * (1 - $p));

        $content = new ArrayCollection();
        for ($i = 1; $i <= $n; ++$i) {
            $content->add($this->service->getSingleRandomElementFromProbaCollection($items));
        }

        // then each item is drawn approximately 25 000 times given a 7 sigma margin of error. 
        // The probability of a false positive is then 1 in 10^12
        foreach ($items as $expectedItem => $proba) {
            $this->assertEqualsWithDelta(
                expected: 25000,
                actual: $content->filter(fn ($item) => $item === $expectedItem)->count(),
                delta: 7 * $sigma
            );
        }
    }
}
