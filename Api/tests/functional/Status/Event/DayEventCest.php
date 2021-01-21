<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Event\CycleEvent;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Event\CycleSubscriber;

class DayEventCest
{
    private CycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(CycleSubscriber::class);
    }

    // tests
    public function testChargeStatusDaySubscriber(FunctionalTester $I)
    {
        //Day Increment
        $daedalus = new Daedalus();
        $time = new DateTime();

        $daedalus->setCycle(8);

        $dayEvent = new CycleEvent($daedalus, $time);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room]);

        $status = new ChargeStatus();

        $status
            ->setName('charged')
            ->setPlayer($player)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(1)
            ->setCharge(0)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
        ;

        $I->haveInRepository($status);

        $dayEvent->setStatus($status);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(1, $status->getCharge());

        //Day decrement
        $dayEvent = new CycleEvent($daedalus, $time);

        $status = new ChargeStatus();

        $status
            ->setName('charged')
            ->setPlayer($player)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(0)
            ->setCharge(1)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
        ;

        $I->haveInRepository($status);

        $dayEvent->setStatus($status);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(0, $status->getCharge());

        //Day reset
        $dayEvent = new CycleEvent($daedalus, $time);

        $status = new ChargeStatus();

        $status
            ->setName('charged')
            ->setPlayer($player)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(5)
            ->setCharge(1)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
        ;

        $I->haveInRepository($status);

        $dayEvent->setStatus($status);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(5, $status->getCharge());
    }
}
