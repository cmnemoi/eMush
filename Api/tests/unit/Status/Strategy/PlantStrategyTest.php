<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\PlantStrategy;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class PlantStrategyTest extends TestCase
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

        $this->strategy = new PlantStrategy($this->statusService);
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

        $this->strategy->execute($status, new Daedalus());

        $this->assertEquals(1, $status->getCharge());

        $status->setCharge(10);
        $this->statusService->shouldReceive('persist')->once();

        $this->strategy->execute($status, new Daedalus());

        $this->assertEquals(10, $status->getCharge());

        $status->setAutoRemove(true);
        $this->statusService->shouldReceive('delete')->once();

        $result = $this->strategy->execute($status, new Daedalus());

        $this->assertNull($result);
    }

    private function createStatus(): ChargeStatus
    {
        $status = new ChargeStatus(new Player());
        $status
            ->setCharge(0)
            ->setThreshold(10)
            ->setStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
        ;

        return $status;
    }
}
