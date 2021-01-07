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
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Event\CycleSubscriber;

class CycleEventCest
{
    private CycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(CycleSubscriber::class);
    }

    // tests
    public function testChargeStatusCycleSubscriber(FunctionalTester $I)
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

        $I->haveInRepository($status);
        $id = $status->getId();

        $cycleEvent->setStatus($status);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->dontSeeInRepository(ChargeStatus::class, ['id' => $id]);

        $I->assertEquals(1, $status->getCharge());
    }

    public function testLieDownStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room]);

        $actionPointBefore = $player->getActionPoint();

        $time = new DateTime();

        $cycleEvent = new CycleEvent($daedalus, $time);

        $status = new Status();

        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setPlayer($player)
        ;

        $cycleEvent->setStatus($status);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($actionPointBefore + 1, $player->getActionPoint());
    }
}
