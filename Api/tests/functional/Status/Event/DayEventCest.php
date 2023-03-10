<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
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
        // Day Increment
        $daedalus = new Daedalus();
        $time = new \DateTime();
        $player = $I->have(Player::class);

        $daedalus->setCycle(1);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::ELECTRIC_CHARGES)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(false)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_INCREMENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($player, $statusConfig);
        $status
            ->setCharge(0)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(1, $status->getCharge());

        // Day decrement
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::FROZEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(1)
            ->setAutoRemove(false)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_DECREMENT)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($player, $statusConfig);
        $status
            ->setCharge(1)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(0, $status->getCharge());

        // Day reset
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::DECOMPOSING)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setMaxCharge(5)
            ->setAutoRemove(true)
            ->setChargeStrategy(ChargeStrategyTypeEnum::DAILY_RESET)
            ->buildName(GameConfigEnum::TEST)
        ;
        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($player, $statusConfig);
        $status
            ->setCharge(1)
        ;

        $I->haveInRepository($status);

        $dayEvent = new StatusCycleEvent($status, new Player(), [EventEnum::NEW_DAY], $time);

        $this->cycleSubscriber->onNewCycle($dayEvent);

        $I->assertEquals(5, $status->getCharge());
    }
}
