<?php

namespace Mush\Test\Game\Service;

use DateTime;
use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CycleServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;

    private CycleService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->service = new CycleService($this->eventDispatcher);
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

        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime('2020-10-09 00:30:00.0 UTC'))
        ;

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $gameConfig));

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $gameConfig));

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $gameConfig));

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $gameConfig));

        $timeZone = 'Europe/London';
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;
        $daedalus
            ->setGameConfig($gameConfig);

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $gameConfig));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $gameConfig));

        //test different cycle configs
        $timeZone = 'Europe/Paris';
        $gameConfig = new GameConfig();
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(1 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime('2020-10-10 20:00:00.0 UTC'))
        ;
        $this->assertEquals(7, $this->service->getCycleFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $gameConfig));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime('2020-10-10 10:00:00.0 UTC'))
        ;
        $this->assertEquals(5, $this->service->getCycleFromDate(new \DateTime('2020-10-10 12:10:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(, $this->service->getDayFromDate(new \DateTime('2020-10-10 12:10:00.0 Europe/Paris'), $gameConfig));

        //longer cycles
        $timeZone = 'Europe/Paris';
        $gameConfig = new GameConfig();
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(4 * 60)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime('2020-10-10 20:00:00.0 UTC'))
        ;
        $this->assertEquals(6, $this->service->getCycleFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $gameConfig));

        $this->assertEquals(6, $this->service->getCycleFromDate(new \DateTime('2020-10-13 22:10:00.0 Europe/Paris'), $gameConfig));
        $this->assertEquals(1, $this->service->getDayFromDate(new \DateTime('2020-10-13 22:10:00.0 Europe/Paris'), $gameConfig));
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
            ->setUpdatedAt(new DateTime("2020-10-09 23:30:00.0 {$timeZone}"))
            ->setCycle(8)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 23:31:00.0 {$timeZone}"), $daedalus));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setUpdatedAt(new DateTime("2020-10-09 00:30:00.0 {$timeZone}"))
            ->setCycle(1)
        ;

        $this->assertEquals(2, $this->service->handleCycleChange(new DateTime("2020-10-09 06:30:00.0 {$timeZone}"), $daedalus));
        $this->assertEquals(8, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        //1 hours cycles => 24 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(24)
            ->setCycleLength(1 * 60)
        ;
        $this->assertEquals(24, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        //12 hours cycles => 2 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(12 * 60)
        ;
        $this->assertEquals(2, $this->service->handleCycleChange(new DateTime("2020-10-10 00:30:00.0 {$timeZone}"), $daedalus));

        //24 hours cycles
        $gameConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(24 * 60)
        ;
        //31 days in October
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
            ->setUpdatedAt(new DateTime("2020-10-09 23:30:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(1)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 00:31:00.0 {$timeZone}"), $daedalus));
        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-08 23:31:00.0 {$timeZone}"), $daedalus));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime("2020-10-08 02:30:00.0 {$timeZone}"))
            ->setUpdatedAt(new DateTime("2020-10-09 02:30:00.0 {$timeZone}"))
            ->setDay(2)
            ->setCycle(2)
        ;

        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 02:31:00.0 {$timeZone}"), $daedalus));
        $this->assertEquals(0, $this->service->handleCycleChange(new DateTime("2020-10-09 03:31:00.0 {$timeZone}"), $daedalus));
    }
}
