<?php

namespace Mush\Tests\Status\Event;

use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\CycleEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Event\CycleSubscriber;

class CycleEventTest extends \Codeception\Test\Unit
{
    /**
     * @var \App\Tests\FunctionalTester
     */
    protected $tester;

    private CycleSubscriber $cycleSubscriber;

    protected function _before()
    {
        $this->cycleSubscriber = $this->tester->grabService(CycleSubscriber::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testChargeStatusCycleSubscriber()
    {
        $daedalus = new Daedalus();
        $time = new DateTime();

        $cycleEvent = new CycleEvent($daedalus, $time);

        $status = new ChargeStatus();


        $status
            ->setName('charged')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(1)
            ->setCharge(0)
            ->setAutoRemove(true)
            ->setStrategy(ChargeStrategyTypeEnum::CYCLE_INCREMENT)
        ;

        $this->tester->haveInRepository($status);
        $id = $status->getId();

        $cycleEvent->setStatus($status);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $this->tester->dontSeeInRepository(ChargeStatus::class, ['id' => $id]);

        $this->assertEquals(1, $status->getCharge());
    }
}