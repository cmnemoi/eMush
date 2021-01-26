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

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(2, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $daedalus));

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(2, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $daedalus));

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $daedalus));
        $this->assertEquals(2, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $daedalus));

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $daedalus));
        $this->assertEquals(3, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $daedalus));

        $timeZone = 'Europe/London';
        $gameConfig
            ->setCyclePerGameDay(8)
            ->setCycleLength(3 * 60)
            ->setTimeZone($timeZone)
        ;
        $daedalus
            ->setGameConfig($gameConfig);

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London'), $daedalus));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London'), $daedalus));

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
        $this->assertEquals(7, $this->service->getCycleFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(1, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $daedalus));

        $daedalus = new Daedalus();
        $daedalus
            ->setGameConfig($gameConfig)
            ->setCreatedAt(new DateTime('2020-10-10 10:00:00.0 UTC'))
        ;
        $this->assertEquals(5, $this->service->getCycleFromDate(new \DateTime('2020-10-10 12:10:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(1, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 12:10:00.0 Europe/Paris'), $daedalus));

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
        $this->assertEquals(6, $this->service->getCycleFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(1, $this->service->getGameDayFromDate(new \DateTime('2020-10-10 22:10:00.0 Europe/Paris'), $daedalus));

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-13 22:10:00.0 Europe/Paris'), $daedalus));
        $this->assertEquals(3, $this->service->getGameDayFromDate(new \DateTime('2020-10-13 22:10:00.0 Europe/Paris'), $daedalus));
    }

    public function testHandleCycleChange()
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

        $daedalus->setCycle(1);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
        ;

        $daedalus->setUpdatedAt(new \DateTime('-6 hour'));
        $this->assertEquals(2, $this->service->handleCycleChange($daedalus));

        $daedalus->setUpdatedAt(new \DateTime('-1 day'));
        $this->assertEquals(8, $this->service->handleCycleChange($daedalus));

        //1 hours cycles => 24 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(24)
            ->setCycleLength(1 * 60)
        ;
        $this->assertEquals(24, $this->service->handleCycleChange($daedalus));

        //12 hours cycles => 2 cycle elapsed
        $gameConfig
            ->setCyclePerGameDay(2)
            ->setCycleLength(12 * 60)
        ;
        $this->assertEquals(2, $this->service->handleCycleChange($daedalus));


        //@TODO add test with entering DST
    }
}
