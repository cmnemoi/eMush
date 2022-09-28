<?php

namespace Mush\Test\Game\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\CycleService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\TestCase;

class CycleServiceTest extends TestCase
{
    /** @var EventDispatcherInterface|Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    /** @var EntityManagerInterface|Mockery\Mock */
    private EntityManagerInterface $entityManager;

    private CycleService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(eventDispatcherInterface::class);
        $this->entityManager = Mockery::mock(EntityManagerInterface::class);

        $this->entityManager->shouldReceive('persist');
        $this->entityManager->shouldReceive('flush');

        $this->service = new CycleService($this->entityManager, $this->eventDispatcher);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testGetCycleTimezone()
    {
        $timeZone = 'Europe/Paris';

        $gameConfig = new GameConfig();

        // shorter cycles
        $timeZone = 'Europe/Paris';
        $gameConfig = new GameConfig();
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(30)
            ->setTimeZone($timeZone)
        ;

        $this->assertEquals(4, $this->service->getInDayCycleFromDate(new \DateTime('2020-10-10 01:45:00.0 Europe/Paris'), $gameConfig));
    }

    public function testHandleCycleChange()
    {
        $timeZone = 'Europe/Paris';

        $this->eventDispatcher
            ->shouldReceive('dispatch')
        ;

        $gameConfig = new GameConfig();

        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-09 23:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-10-09 21:00:00.0 {$timeZone}"))
            ->setCycle(8)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 23:31:00.0 {$timeZone}"), $daedalus));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"))
            ->setCycle(1)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(2, $this->service->handleCycleChange(new DateTime("2020-10-09 06:30:00.0 {$timeZone}"), $daedalus));

        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        $this->assertEquals(8, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        // 1 hours cycles => 24 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(24)
            ->setCycleLength(1 * 60)
        ;
        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        $this->assertEquals(24, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        // 12 hours cycles => 2 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(12 * 60)
        ;
        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        $this->assertEquals(2, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        // 24 hours cycles
        $gameConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(24 * 60)
        ;
        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));

        // 31 days in October
        $this->assertEquals(31, $this->service->handleCycleChange(new DateTime("2020-11-09 00:30:00.0 {$timeZone}"), $daedalus));
    }

    public function testDateChange()
    {
        $timeZone = 'Europe/Paris';

        $this->eventDispatcher
            ->shouldReceive('dispatch')
        ;

        $gameConfig = new GameConfig();

        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-08 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 00:31:00.0 {$timeZone}"), $daedalus));
        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));
        $this->assertEquals(1, $this->service->handleCycleChange(new DateTime("2020-10-09 03:31:00.0 {$timeZone}"), $daedalus));
        $daedalus->setCycleStartedAt(new DateTime("2020-10-09 00:00:00.0 {$timeZone}"));
        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-08 23:31:00.0 {$timeZone}"), $daedalus));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-08 02:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-10-09 03:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(2)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 02:31:00.0 {$timeZone}"), $daedalus));
        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 03:31:00.0 {$timeZone}"), $daedalus));

        // in case entering DST in between
        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2021-03-27 00:00:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2021-03-28 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2021-03-28 03:31:00.0 {$timeZone}"), $daedalus));
        $daedalus->setCycleStartedAt(new DateTime("2021-03-28 00:00:00.0 {$timeZone}"));
        $this->assertEquals(1, $this->service->handleCycleChange(new DateTime("2021-03-28 04:31:00.0 {$timeZone}"), $daedalus));

        // in case exiting DST in between
        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-24 00:00:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-10-25 00:00:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1)
            ->setGameStatus(GameStatusEnum::STARTING)
        ;

        $this->assertEquals(1, $this->service->handleCycleChange(new DateTime("2020-10-25 03:31:00.0 {$timeZone}"), $daedalus));

        $daedalus->setCycleStartedAt(new DateTime("2020-10-25 00:00:00.0 {$timeZone}"));
        $this->assertEquals(2, $this->service->handleCycleChange(new DateTime("2020-10-25 05:31:00.0 {$timeZone}"), $daedalus));
    }

    public function testStandbyDaedalus()
    {
        $timeZone = 'Europe/Paris';

        $this->eventDispatcher
            ->shouldReceive('dispatch')
        ;

        $gameConfig = new GameConfig();

        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setCycleStartedAt(new DateTime("2020-09-09 24:00:00.0 {$timeZone}"))
            ->setCycle(0)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 23:31:00.0 {$timeZone}"), $daedalus));
    }

    public function testGetDaedalusStartingCycleDate()
    {
        $timeZone = 'Europe/Paris';
        // Simple ship
        $gameConfig = new GameConfig();
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-08-09 00:30:00.0 {$timeZone}"))
            ->setCycle(1)
        ;
        $this->assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new DateTime("2020-08-09 00:00:00.0 {$timeZone}"));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-08-09 23:30:00.0 {$timeZone}"))
            ->setCycle(8)
        ;
        $this->assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new DateTime("2020-08-09 21:00:00.0 {$timeZone}"));

        // Change cycle length
        $gameConfig = new GameConfig();
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(1 * 60)
            ->setTimeZone($timeZone)
        ;
        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-09-09 23:30:00.0 {$timeZone}"))
            ->setCycle(8)
        ;
        $this->assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new DateTime("2020-09-09 23:00:00.0 {$timeZone}"));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-09-09 11:30:00.0 {$timeZone}"))
            ->setCycle(4)
        ;
        $this->assertEquals($this->service->getDaedalusStartingCycleDate($daedalus), new DateTime("2020-09-09 11:00:00.0 {$timeZone}"));
    }
}
