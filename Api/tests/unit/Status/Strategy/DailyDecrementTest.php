<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Game\Service\CycleServiceInterface;
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
        $this->cycleService = Mockery::mock(CycleServiceInterface::class);

        $this->strategy = new DailyDecrement($this->statusService, $this->cycleService);
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

        $this->cycleService->shouldReceive('getCycleFromDate')->andReturn(2)->once();
        $this->statusService->shouldReceive('persist')->once();

        $this->strategy->execute($status);

        $this->assertEquals(10, $status->getCharge());

        $this->statusService->shouldReceive('persist')->once();
        $this->cycleService->shouldReceive('getCycleFromDate')->andReturn(1)->once();

        $this->strategy->execute($status);

        $this->assertEquals(9, $status->getCharge());

        $this->statusService->shouldReceive('persist')->once();
        $this->cycleService->shouldReceive('getCycleFromDate')->andReturn(1)->once();
        $status->setCharge(0);

        $this->strategy->execute($status);

        $this->assertEquals(0, $status->getCharge());

        $status->setAutoRemove(true);
        $this->statusService->shouldReceive('delete')->once();
        $this->cycleService->shouldReceive('getCycleFromDate')->andReturn(1)->once();

        $result = $this->strategy->execute($status);

        $this->assertNull($result);
    }

    private function createStatus(): ChargeStatus
    {
        $status = new ChargeStatus();
        $status
            ->setCharge(10)
            ->setThreshold(0)
            ->setStrategy(ChargeStrategyTypeEnum::PLANT)
        ;

        return $status;
    }
}
