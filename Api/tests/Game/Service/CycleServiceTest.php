<?php


namespace Mush\Test\Game\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleService;
use Mush\Game\Service\GameConfigServiceInterface;
use \Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CycleServiceTest extends TestCase
{
    /** @var EventDispatcherInterface | Mockery\Mock */
    private EventDispatcherInterface $eventDispatcher;
    private GameConfig $gameConfig;
    private CycleService $service;

    /**
     * @before
     */
    public function before()
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $gameConfigService = Mockery::mock(GameConfigServiceInterface::class);
        $this->gameConfig = new GameConfig();
        $gameConfigService->shouldReceive('getConfig')->andReturn($this->gameConfig)->once();

        $this->service = new CycleService($gameConfigService, $this->eventDispatcher);

    }

    public function testGetCycleTimezone()
    {
        $timeZone = 'Europe/Paris';
        $this->gameConfig
            ->setCycleLength(3)
            ->setTimeZone($timeZone)
        ;

        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris')));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris')));
        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London')));
        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London')));

        $timeZone = 'Europe/London';
        $this->gameConfig
            ->setCycleLength(3)
            ->setTimeZone($timeZone)
        ;

        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/Paris')));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/Paris')));
        $this->assertEquals(1, $this->service->getCycleFromDate(new \DateTime('2020-10-10 00:30:00.0 Europe/London')));
        $this->assertEquals(8, $this->service->getCycleFromDate(new \DateTime('2020-10-10 23:30:00.0 Europe/London')));
    }

    public function testHandleCycleChange()
    {
        $timeZone = 'Europe/Paris';
        $this->gameConfig
            ->setCycleLength(3)
            ->setTimeZone($timeZone)
        ;

        $daedalus = new Daedalus();
        $daedalus->setCycle(1);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
        ;

        $daedalus->setUpdatedAt(new \DateTime('-6 hour'));
        $this->assertEquals(2, $this->service->handleCycleChange($daedalus));

        $daedalus->setUpdatedAt(new \DateTime('-1 day'));
        $this->assertEquals(8, $this->service->handleCycleChange($daedalus));

        //1 hours cycles => 24 cycle elapsed
        $this->gameConfig->setCycleLength(1);
        $this->assertEquals(24, $this->service->handleCycleChange($daedalus));

        //12 hours cycles => 2 cycle elapsed
        $this->gameConfig->setCycleLength(12);
        $this->assertEquals(2, $this->service->handleCycleChange($daedalus));
    }

}