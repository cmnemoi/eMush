<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EntityManagerInterface | Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var DiseaseConfigRepository | Mockery\Mock */
    private DiseaseConfigRepository $diseaseConfigRepository;

    /** @var RandomServiceInterface | Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);
        $this->diseaseConfigRepository = Mockery::mock(DiseaseConfigRepository::class);
        $this->randomService = Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            $this->entityManager,
            $this->diseaseConfigRepository,
            $this->randomService,
            $this->eventDispatcher,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testCreateDiseaseFromNameAndWithDiseaseConfigDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this
            ->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDelayMin(), $diseaseConfig->getDelayMin() + $diseaseConfig->getDelayLength()])
            ->andReturn(4)
            ->once()
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithArgumentsDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([10, 15])
            ->andReturn(4)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 10, 5);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithoutDelay()
    {
        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $daedalus->setGameConfig($gameConfig);
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->diseaseConfigRepository
            ->shouldReceive('findOneBy')
            ->andReturn($diseaseConfig)
            ->once()
        ;

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDiseasePointMin(), $diseaseConfig->getDiseasePointMin() + $diseaseConfig->getDiseasePointLength()])
            ->andReturn(4)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')->twice();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 0, 0);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $disease->getStatus());
    }

    public function testHandleDiseaseForCause()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $this->diseaseConfigRepository
            ->shouldReceive('findByCauses')
            ->andReturn([$diseaseConfig])
            ->twice()
        ;

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([])
            ->once()
        ;

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);

        $this->randomService
            ->shouldReceive('getRandomElements')
            ->andReturn([$diseaseConfig])
            ->once()
        ;

        $this->entityManager->shouldReceive([
            'persist' => null,
            'flush' => null,
        ])->once();

        $this->playerDiseaseService->handleDiseaseForCause('cause', $player);
    }

    public function testHandleNewCycle()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(10)
        ;

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(9, $diseasePlayer->getDiseasePoint());
    }

    public function testHandleNewCycleSpontaneousCure()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(0, $diseasePlayer->getDiseasePoint());
    }

    public function testHandleNewCycleIncubatedDiseaseAppear()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(10, $diseasePlayer->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $diseasePlayer->getStatus());
    }
}
