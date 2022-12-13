<?php

namespace Mush\Tests\unit\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var RandomServiceInterface|Mockery\Mock */
    private RandomServiceInterface $randomService;

    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @before
     */
    public function before()
    {
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $this->playerDiseaseService = new PlayerDiseaseService(
            $this->entityManager,
            $this->randomService,
            $this->eventDispatcher,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testCreateDiseaseFromNameAndWithDiseaseConfigDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDelayMin(4)->setDelayLength(4)
            ->setDiseaseName('name')
        ;

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this
            ->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDelayMin(), $diseaseConfig->getDelayMin() + $diseaseConfig->getDelayLength()])
            ->andReturn(4)
            ->once()
        ;
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, DiseaseCauseEnum::INCUBATING_END);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithArgumentsDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);
        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([10, 15])
            ->andReturn(4)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 'cause', 10, 5);

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::INCUBATING, $disease->getStatus());
    }

    public function testCreateDiseaseFromNameAndWithoutDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());
        $player = new Player();
        $player->setDaedalus($daedalus);

        $this->entityManager->shouldReceive(['persist' => null, 'flush' => null]);

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDiseasePointMin(), $diseaseConfig->getDiseasePointMin() + $diseaseConfig->getDiseasePointLength()])
            ->andReturn(4)
            ->once();
        $this->eventDispatcher->shouldReceive('dispatch')->twice();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, 'reason');

        $this->assertInstanceOf(PlayerDisease::class, $disease);
        $this->assertEquals($diseaseConfig, $disease->getDiseaseConfig());
        $this->assertEquals($player, $disease->getPlayer());
        $this->assertEquals(4, $disease->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $disease->getStatus());
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

    public function testHandleNewCycleIncubatedDiseaseAppearAndOverrodeDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setOverride([InjuryEnum::BROKEN_SHOULDER]);
        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::INCUBATING)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(1)
        ;

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setDiseaseName(InjuryEnum::BROKEN_SHOULDER);
        $diseasePlayer2 = new PlayerDisease();
        $diseasePlayer2
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig2)
            ->setDiseasePoint(1)
        ;
        $player->addMedicalCondition($diseasePlayer2);

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (DiseaseEvent $event) => (
                $event->getPlayerDisease() === $diseasePlayer) &&
                $event->getReason() === DiseaseCauseEnum::INCUBATING_END
            )
            ->once()
        ;

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->withArgs(fn (DiseaseEvent $event) => (
                $event->getPlayerDisease() === $diseasePlayer2) &&
                $event->getReason() === DiseaseCauseEnum::OVERRODE
            )
            ->once()
        ;
        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $this->assertEquals(10, $diseasePlayer->getDiseasePoint());
        $this->assertEquals(DiseaseStatusEnum::ACTIVE, $diseasePlayer->getStatus());
    }

    public function testHealDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(0)
        ;

        $this->entityManager->shouldReceive('remove')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, 'reason', new \DateTime());
    }

    public function testTreatDisease()
    {
        $daedalus = new Daedalus();
        $player = new Player();
        $player->setDaedalus($daedalus);

        $diseasePlayer = new PlayerDisease();
        $diseasePlayer
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setResistancePoint(1)
        ;

        $this->entityManager->shouldReceive('persist')->once();
        $this->entityManager->shouldReceive('flush')->once();
        $this->eventDispatcher->shouldReceive('dispatch')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, 'reason', new \DateTime());

        $this->assertEquals(0, $diseasePlayer->getResistancePoint());
    }
}
