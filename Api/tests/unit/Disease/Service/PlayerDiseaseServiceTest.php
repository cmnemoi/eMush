<?php

namespace Mush\Tests\unit\Disease\Service;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\InjuryEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Repository\InMemoryPlayerDiseaseRepository;
use Mush\Disease\Service\PlayerDiseaseService;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlayerDiseaseServiceTest extends TestCase
{
    private PlayerDiseaseService $playerDiseaseService;

    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var Mockery\Mock|RandomServiceInterface */
    private RandomServiceInterface $randomService;

    private InMemoryPlayerDiseaseRepository $playerDiseaseRepository;

    /**
     * @before
     */
    public function before()
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->randomService = \Mockery::mock(RandomServiceInterface::class);
        $this->playerDiseaseRepository = new InMemoryPlayerDiseaseRepository();

        $this->playerDiseaseService = new PlayerDiseaseService(
            eventService: $this->eventService,
            randomService: $this->randomService,
            playerDiseaseRepository: $this->playerDiseaseRepository,
        );
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
        $this->playerDiseaseRepository->clear();
    }

    public function testCreateDiseaseFromNameAndWithDiseaseConfigDelay()
    {
        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig
            ->setDelayMin(4)->setDelayLength(4)
            ->setDiseaseName('name');

        $gameConfig = new GameConfig();
        $gameConfig->addDiseaseConfig($diseaseConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, new LocalizationConfig());

        $player = new Player();
        $player->setDaedalus($daedalus);

        $this
            ->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDelayMin(), $diseaseConfig->getDelayMin() + $diseaseConfig->getDelayLength()])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, [DiseaseCauseEnum::INCUBATING_END]);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::INCUBATING, $savedDisease->getStatus());
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

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([10, 15])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->once();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, ['cause'], 10, 5);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertInstanceOf(PlayerDisease::class, $savedDisease);
        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::INCUBATING, $savedDisease->getStatus());
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

        $this->randomService
            ->shouldReceive('random')
            ->withArgs([$diseaseConfig->getDiseasePointMin(), $diseaseConfig->getDiseasePointMin() + $diseaseConfig->getDiseasePointLength()])
            ->andReturn(4)
            ->once();
        $this->eventService->shouldReceive('callEvent')->twice();

        $disease = $this->playerDiseaseService->createDiseaseFromName('name', $player, ['reason']);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($disease->getId());

        self::assertInstanceOf(PlayerDisease::class, $savedDisease);
        self::assertSame($diseaseConfig, $savedDisease->getDiseaseConfig());
        self::assertSame($player, $savedDisease->getPlayer());
        self::assertSame(4, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
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
            ->setDiseasePoint(1);
        $this->eventService->shouldReceive('callEvent')->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(10, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
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
            ->setDiseasePoint(1);

        $diseaseConfig2 = new DiseaseConfig();
        $diseaseConfig2->setDiseaseName(InjuryEnum::BROKEN_SHOULDER);
        $diseasePlayer2 = new PlayerDisease();
        $diseasePlayer2
            ->setPlayer($player)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig2)
            ->setDiseasePoint(1);
        $player->addMedicalCondition($diseasePlayer2);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (DiseaseEvent $event) => (
                    $event->getPlayerDisease() === $diseasePlayer
                )
                    && \in_array(DiseaseCauseEnum::INCUBATING_END, $event->getTags(), true)
            )
            ->once();

        $this->randomService->shouldReceive('random')->andReturn(10);

        $this->eventService
            ->shouldReceive('callEvent')
            ->withArgs(
                static fn (DiseaseEvent $event) => (
                    $event->getPlayerDisease() === $diseasePlayer2
                )
                    && \in_array(DiseaseCauseEnum::OVERRODE, $event->getTags(), true)
            )->once();

        $this->playerDiseaseService->handleNewCycle($diseasePlayer, new \DateTime());

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(10, $savedDisease->getDiseasePoint());
        self::assertSame(DiseaseStatusEnum::ACTIVE, $savedDisease->getStatus());
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
            ->setResistancePoint(0);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, ['reason'], new \DateTime(), VisibilityEnum::PUBLIC);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrNull($diseasePlayer->getId());
        self::assertNull($savedDisease);
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
            ->setResistancePoint(1);

        $this->eventService->shouldReceive('callEvent')->once();

        $this->playerDiseaseService->healDisease($player, $diseasePlayer, ['reason'], new \DateTime(), VisibilityEnum::PUBLIC);

        $savedDisease = $this->playerDiseaseRepository->findByIdOrThrow($diseasePlayer->getId());
        self::assertSame(0, $savedDisease->getResistancePoint());
    }
}
