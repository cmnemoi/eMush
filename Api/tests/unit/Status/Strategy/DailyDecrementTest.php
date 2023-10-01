<?php

namespace Mush\Tests\unit\Status\Strategy;

use Mockery;
use Mush\Game\Enum\EventEnum;
use Mush\Player\Entity\Player;
use Mush\Status\ChargeStrategies\AbstractChargeStrategy;
use Mush\Status\ChargeStrategies\DailyDecrement;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class DailyDecrementTest extends TestCase
{
    /** @var StatusServiceInterface|Mockery\Mock */
    private StatusServiceInterface $statusService;

    private AbstractChargeStrategy $strategy;

    /**
     * @before
     */
    public function before()
    {
        $this->statusService = \Mockery::mock(StatusServiceInterface::class);

        $this->strategy = new DailyDecrement($this->statusService);
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testDecrement()
    {
        $status = $this->createStatus();
        $time = new \DateTime();

        $player = new Player();
        $player
            ->addStatus($status)
        ;

        $this->statusService->shouldReceive('updateCharge')->with($status, -1, [EventEnum::NEW_DAY], $time)->once();

        $this->strategy->execute($status, [EventEnum::NEW_DAY], $time);

        $this->strategy->execute($status, [EventEnum::NEW_CYCLE], $time);
    }

    private function createStatus(): ChargeStatus
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->setStatusName('status')
        ;
        $status = new ChargeStatus(new Player(), $statusConfig);

        $status->getVariableByName($status->getName())->setValue(10);

        return $status;
    }
}
