<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Listener\StatusCycleSubscriber;

class DayEventCest
{
    private StatusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
    }

    // tests
    public function testChargeStatusDaySubscriber(FunctionalTester $I)
    {
        //Day Increment
        $daedalus = new Daedalus();
        $time = new DateTime();
        $player = $I->have(Player::class);

        $daedalus->setCycle(1);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        $status = new ChargeStatus($player);

        $status
            ->setName('charged')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(1)
            ->setCharge(0)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), EventEnum::NEW_DAY, $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(1, $status->getCharge());

        //Day decrement
        $status = new ChargeStatus($player);

        $status
            ->setName('charged')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(0)
            ->setCharge(1)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), EventEnum::NEW_DAY, $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(0, $status->getCharge());

        //Day reset

        $status = new ChargeStatus($player);

        $status
            ->setName('charged')
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setThreshold(5)
            ->setCharge(1)
            ->setAutoRemove(false)
            ->setStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), EventEnum::NEW_DAY, $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(5, $status->getCharge());
    }
}
