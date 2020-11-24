<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\CycleDecrement;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class CycleDecrementTest extends TestCase
{
    /** @var StatusServiceInterface | Mockery\Mock */
    private StatusServiceInterface $statusService;
    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new CycleDecrement($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testIncrement()
    {
        $status = $this->createStatus();

        $this->statusService->shouldReceive('persist')->once();

        $this->strategy->execute($status);

        $this->assertEquals(9, $status->getCharge());

        $status->setCharge(0);
        $this->statusService->shouldReceive('persist')->once();

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
            ->setStrategy(ChargeStrategyTypeEnum::CYCLE_DECREMENT)
        ;

        return $status;
    }
}
