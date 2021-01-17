<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\DailyDecrement;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class DailyDecrementTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    /** @var CycleServiceInterface | Mockery\Mock */
    private CycleServiceInterface $cycleService;
    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new DailyDecrement($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testDecrement()
    {
        $status = $this->createStatus();

        $daedalus = new Daedalus();

        $player = new Player();
        $player->setDaedalus($daedalus);
        $status->setPlayer($player);

        $daedalus->setCycle(2);
        $this->statusService->shouldReceive('persist')->once();

        $this->strategy->execute($status);

        $this->assertEquals(10, $status->getCharge());

        $this->statusService->shouldReceive('persist')->once();
        $daedalus->setCycle(1);

        $this->strategy->execute($status);

        $this->assertEquals(9, $status->getCharge());

        $this->statusService->shouldReceive('persist')->once();
        $status->setCharge(0);

        $this->strategy->execute($status);

        $this->assertEquals(0, $status->getCharge());

        $status->setAutoRemove(true);
        $this->statusService->shouldReceive('delete')->once();

        $result = $this->strategy->execute($status);

        $this->assertNull($result);
    }

    private function createStatus(): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setCharge(10)
            ->setThreshold(0)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
        ;

        return $status;
    }
}
