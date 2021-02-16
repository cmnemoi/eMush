<?php

namespace Mush\Test\Status\Strategy;

use Mockery;
use Mush\Daedalus\Entity\Daedalus;
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
        $player
            ->addStatus($status)
            ->setDaedalus($daedalus)
        ;

        $daedalus->setCycle(1);
        $this->statusService->shouldReceive('changeCharge')->with($status, -1)->once();

        $this->strategy->execute($status, $daedalus);

        $daedalus->setCycle(2);

        $this->strategy->execute($status, $daedalus);
    }

    private function createStatus(): ChargeStatus
    {
        $status = new ChargeStatus(new Player());
        $status
            ->setCharge(10)
            ->setThreshold(0)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
        ;

        return $status;
    }
}
