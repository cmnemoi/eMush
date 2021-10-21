<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\CycleIncrement;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class CycleIncrementTest extends TestCase
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;
    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new CycleIncrement($this->statusService);
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

        $this->statusService->shouldReceive('updateCharge')->with($status, 1)->once();

        $this->strategy->execute($status, EventEnum::NEW_CYCLE);
    }

    private function createStatus(): ChargeStatus
    {
        $status = new ChargeStatus(new Player(), EquipmentStatusEnum::PLANT_YOUNG);
        $status
            ->setCharge(0)
            ->setThreshold(10)
            ->setStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
        ;

        return $status;
    }
}
