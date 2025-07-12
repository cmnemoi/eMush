<?php

namespace Mush\Tests\unit\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleService;
use Mush\Game\Service\EventServiceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * @internal
 */
final class CycleServiceTest extends TestCase
{
    /** @var EventServiceInterface|Mockery\Mock */
    private EventServiceInterface $eventService;

    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    /** @var LockFactory|Mockery\Spy */
    private LockFactory $lockFactory;

    /** @var LoggerInterface|Mockery\Mock */
    private LoggerInterface $logger;

    private CycleService $service;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->eventService = \Mockery::mock(EventServiceInterface::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->lockFactory = \Mockery::mock(LockFactory::class);
        $this->logger = \Mockery::mock(LoggerInterface::class);

        $this->entityManager->shouldReceive('beginTransaction');
        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('commit');

        $lockInterface = \Mockery::mock(LockInterface::class);
        $lockInterface->shouldReceive('acquire')->andReturn(true);

        $lockInterface->shouldReceive('release');

        $this->lockFactory->shouldReceive('createLock')->andReturn($lockInterface);

        $this->service = new CycleService($this->entityManager, $this->eventService, $this->lockFactory, $this->logger);
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testGetCycleTimezone()
    {
        $timeZone = 'Europe/Paris';

        $localizationConfig = new LocalizationConfig();

        // shorter cycles
        $timeZone = 'Europe/Paris';
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setTimeZone($timeZone);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(30);

        $gameConfig = new GameConfig();
        $daedalus = new Daedalus();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);

        self::assertSame(4, $this->service->getInDayCycleFromDate(new \DateTime('2020-10-10 01:45:00.0 Europe/Paris'), $daedalus));
    }

    public function testHandleDaedalusAndExplorationCycleChanges()
    {
        $timeZone = 'Europe/Paris';

        $this->eventService
            ->shouldReceive('callEvent');

        $localizationConfig = new LocalizationConfig();
        $localizationConfig
            ->setTimeZone($timeZone);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60);

        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-09 23:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-10-09 21:00:00.0 {$timeZone}"))
            ->setCycle(8);

        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 23:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"))
            ->setCycle(1);

        self::assertSame(2, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 06:30:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        self::assertSame(8, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        // 1 hours cycles => 24 cycle elapsed
        $daedalusConfig
            ->setCyclePerGameDay(24)
            ->setCycleLength(1 * 60);
        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        self::assertSame(24, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        // 12 hours cycles => 2 cycle elapsed
        $daedalusConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(12 * 60);
        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        self::assertSame(2, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        // 24 hours cycles
        $daedalusConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(24 * 60);
        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        // 31 days in October
        self::assertSame(31, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-11-09 00:30:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
    }

    public function testDateChange()
    {
        $timeZone = 'Europe/Paris';

        $this->eventService
            ->shouldReceive('callEvent');

        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setTimeZone($timeZone);
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-08 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1);

        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 00:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));
        self::assertSame(1, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 03:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
        $daedalus->setCycleStartedAt(new \DateTime("2020-10-09 00:00:00.0 {$timeZone}"));
        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-08 23:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-08 02:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-10-09 03:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(2);

        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 02:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 03:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        // in case entering DST in between
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2021-03-27 00:00:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2021-03-28 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1);

        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2021-03-28 03:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
        $daedalus->setCycleStartedAt(new \DateTime("2021-03-28 00:00:00.0 {$timeZone}"));
        self::assertSame(1, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2021-03-28 04:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        // in case exiting DST in between
        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalusInfo->setGameStatus(GameStatusEnum::STARTING);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-24 00:00:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-10-25 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1);

        self::assertSame(1, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-25 03:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);

        $daedalus->setCycleStartedAt(new \DateTime("2020-10-25 00:00:00.0 {$timeZone}"));
        self::assertSame(2, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-25 05:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
    }

    public function testStandbyDaedalus()
    {
        $timeZone = 'Europe/Paris';

        $this->eventService
            ->shouldReceive('callEvent');

        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setTimeZone($timeZone);

        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new \DateTime("2020-09-09 24:00:00.0 {$timeZone}"))
            ->setCycle(0);

        self::assertSame(0, $this->service->handleDaedalusAndExplorationCycleChanges(new \DateTime("2020-10-09 23:31:00.0 {$timeZone}"), $daedalus)->daedalusCyclesElapsed);
    }

    public function testGetDaedalusStartingCycleDate()
    {
        $timeZone = 'Europe/Paris';
        // Simple ship
        $localizationConfig = new LocalizationConfig();
        $localizationConfig->setTimeZone($timeZone);
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60);
        $gameConfig = new GameConfig();
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-08-09 00:30:00.0 {$timeZone}"))
            ->setCycle(1);
        self::assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new \DateTime("2020-08-09 00:00:00.0 {$timeZone}"));

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-08-09 23:30:00.0 {$timeZone}"))
            ->setCycle(8);
        self::assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new \DateTime("2020-08-09 21:00:00.0 {$timeZone}"));

        // Change cycle length
        $daedalusConfig = new DaedalusConfig();
        $daedalusConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(60);
        $gameConfig->setDaedalusConfig($daedalusConfig);

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalus
            ->setCreatedAt(new \DateTime('2020-09-09 21:30:00.0 UTC'))
            ->setCycle(8)
            ->setDay(3);
        self::assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new \DateTime("2020-09-09 23:00:00.0 {$timeZone}"));

        $daedalus = new Daedalus();
        new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $daedalus
            ->setCreatedAt(new \DateTime("2020-09-09 11:30:00.0 {$timeZone}"))
            ->setCycle(4);
        self::assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new \DateTime("2020-09-09 11:00:00.0 {$timeZone}"));
    }
}
