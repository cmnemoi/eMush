<?php

namespace Mush\Tests\unit\Status\Strategy;

use Mockery;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\PlantStrategy;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PlantStrategyTest extends TestCase
{
    /** @var Mockery\Mock|StatusServiceInterface */
    private StatusServiceInterface $statusService;
    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new PlantStrategy($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testIncrement()
    {
        $status = $this->createStatus();
        $time = new \DateTime();

        $this->statusService->shouldReceive('updateCharge')
            ->with($status, 1, [EventEnum::NEW_CYCLE], $time)
            ->andReturn($status)
            ->once();

        $this->strategy->execute($status, [EventEnum::NEW_CYCLE], $time);
    }

    public function testMaturity()
    {
        $status = $this->createStatus();
        $status->setCharge(10);
        $time = new \DateTime();

        $this->statusService->shouldReceive('updateCharge')
            ->with($status, 1, [EventEnum::NEW_CYCLE], $time)
            ->andReturn($status)
            ->once();
        $this->statusService->shouldReceive('removeStatus')->once();

        $this->strategy->execute($status, [EventEnum::NEW_CYCLE], $time);
    }

    private function createStatus(): ChargeStatus
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setChargeStrategy(ChargeStrategyTypeEnum::GROWING_PLANT)
            ->setMaxCharge(10)
            ->setStatusName('status');
        $status = new ChargeStatus(new Player(), $statusConfig);
        $status->getVariableByName($status->getName())->setValue(0);
        $status->getVariableByName($status->getName())->setMaxValue(10);

        return $status;
    }
}
