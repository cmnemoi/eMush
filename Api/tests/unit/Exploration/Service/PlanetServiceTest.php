<?php

namespace Mush\Tests\unit\Exploration\Service;

use Doctrine\ORM\EntityManager;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Exploration\Entity\PlanetSectorConfig;
use Mush\Exploration\Entity\SpaceCoordinates;
use Mush\Exploration\Enum\SpaceOrientationEnum;
use Mush\Exploration\Repository\PlanetRepository;
use Mush\Exploration\Service\PlanetService;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\DifficultyEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlanetServiceTest extends TestCase
{
    /** @var Mockery\Mock|PlanetRepository */
    private PlanetRepository $planetRepository;

    /** @var EntityManager|Mockery\Mock */
    private EntityManager $entityManager;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private PlanetService $service;
    private Player $player;
    private Daedalus $daedalus;
    private GameConfig $gameConfig;

    /**
     * @before
     */
    public function before()
    {
        $this->planetRepository = \Mockery::mock(PlanetRepository::class);
        $this->entityManager = \Mockery::mock(EntityManager::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);

        $this->service = new PlanetService(
            $this->entityManager,
            $this->planetRepository,
            $this->randomService
        );

        // Given a Daedalus
        $this->daedalus = new Daedalus();
        $this->gameConfig = new GameConfig();
        new DaedalusInfo($this->daedalus, $this->gameConfig, new LocalizationConfig());
        $difficultyConfig = new DifficultyConfig();
        $difficultyConfig->setDifficultyModes([DifficultyEnum::NORMAL => 1, DifficultyEnum::HARD => 5, DifficultyEnum::VERY_HARD => 10]);
        $this->gameConfig->setDifficultyConfig($difficultyConfig);

        // Given a player on this Daedalus
        $this->player = new Player();
        $this->player->setDaedalus($this->daedalus);
        $characterConfig = new CharacterConfig();
        $characterConfig->setMaxDiscoverablePlanets(2);
        new PlayerInfo($this->player, new User(), $characterConfig);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testSimpleGeneration()
    {
        // Given a single planet sector
        $planetSectorConfig1 = new PlanetSectorConfig();
        $planetSectorConfig1
            ->setName('sector1_test')
            ->setSectorName('sector1')
            ->setWeightAtPlanetGeneration(1)
            ->setWeightAtPlanetAnalysis(1)
            ->setWeightAtPlanetExploration(1)
            ->setMaxPerPlanet(4)
            ->setExplorationEvents([]);
        $this->gameConfig->setPlanetSectorConfigs([$planetSectorConfig1]);

        // when creating a planet
        // then random name generation
        $this->randomPlanetNameGenerationExpectations();
        // then random coordinates generation
        $this->randomPlanetPositionExpectations();
        // then draw the size of the planet (here the size is 2 * 1 + 2)
        $this->randomService->shouldReceive('random')->with(0, 5)->andReturn(1)->once();

        // then pick sectors
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 1st sector pick is 1
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 2nd sector pick is 1
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 3rd sector pick is 1
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 4th sector pick is 1
        $this->entityManager->shouldReceive('refresh')->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $planet = $this->service->createPlanet($this->player);

        self::assertSame(4, $planet->getSize());
        self::assertCount(4, $planet->getSectors());
    }

    public function testMaxSectorPerPlanetTest()
    {
        // Given two planet sector
        $planetSectorConfig1 = new PlanetSectorConfig();
        $planetSectorConfig1
            ->setName('sector1_test')
            ->setSectorName('sector1')
            ->setWeightAtPlanetGeneration(1)
            ->setWeightAtPlanetAnalysis(1)
            ->setWeightAtPlanetExploration(1)
            ->setMaxPerPlanet(4)
            ->setExplorationEvents([]);
        $planetSectorConfig2 = new PlanetSectorConfig();
        $planetSectorConfig2
            ->setName('sector2_test')
            ->setSectorName('sector2')
            ->setWeightAtPlanetGeneration(5)
            ->setWeightAtPlanetAnalysis(1)
            ->setWeightAtPlanetExploration(1)
            ->setMaxPerPlanet(1)
            ->setExplorationEvents([]);
        $this->gameConfig->setPlanetSectorConfigs([$planetSectorConfig1, $planetSectorConfig2]);

        // when creating a planet
        // then random name generation
        $this->randomPlanetNameGenerationExpectations();
        // then random coordinates generation
        $this->randomPlanetPositionExpectations();
        // then draw the size of the planet (here the size is 2 * 1 + 2)
        $this->randomService->shouldReceive('random')->with(0, 5)->andReturn(1)->once();

        // then pick sectors
        $this->randomService->shouldReceive('random')->with(0, 5)->andReturn(0)->once(); // 1st sector pick is 1
        $this->randomService->shouldReceive('random')->with(0, 5)->andReturn(4)->once(); // 2nd sector pick is 2
        // then sector config 2 should be removed from the draft
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 3rd sector pick is 1
        $this->randomService->shouldReceive('random')->with(0, 0)->andReturn(0)->once(); // 4th sector pick is 1
        $this->entityManager->shouldReceive('refresh')->twice(); // once per planet sector config

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $planet = $this->service->createPlanet($this->player);

        self::assertSame(4, $planet->getSize());
        self::assertCount(4, $planet->getSectors());
    }

    private function randomPlanetNameGenerationExpectations(): void
    {
        $this->randomService->shouldReceive('random')->with(1, 47)->andReturn(1)->once();
        $this->randomService->shouldReceive('random')->with(1, 33)->andReturn(1)->once();
        $this->randomService->shouldReceive('random')->with(0, 10)->andReturn(0)->once();
        $this->randomService->shouldReceive('random')->with(1, 25)->andReturn(1)->once();
        $this->randomService->shouldReceive('random')->with(0, 40)->andReturn(0)->once();
        $this->randomService->shouldReceive('random')->with(1, 25)->andReturn(1)->once();
        $this->randomService->shouldReceive('random')->with(0, 3)->andReturn(0)->once();
        $this->randomService->shouldReceive('random')->with(1, 34)->andReturn(1)->once();

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
    }

    private function randomPlanetPositionExpectations(): void
    {
        $this->planetRepository
            ->shouldReceive('findAllByDaedalus')
            ->with($this->daedalus)
            ->andReturn([])
            ->once();
        $this->randomService
            ->shouldReceive('rollTwiceAndAverage')
            ->with(2, 7)
            ->andReturn(2)
            ->once();
        $this->randomService
            ->shouldReceive('getRandomElement')
            ->andReturn(new SpaceCoordinates(SpaceOrientationEnum::NORTH, 2))
            ->once();
    }
}
