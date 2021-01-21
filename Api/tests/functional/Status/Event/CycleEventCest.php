<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Event\StatusCycleEvent;
use Mush\Status\Event\StatusCycleSubscriber;

class CycleEventCest
{
    private StatusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(StatusCycleSubscriber::class);
    }

    // tests
    public function testChargeStatusCycleSubscriber(FunctionalTester $I)
    {
        $daedalus = new Daedalus();
        $time = new DateTime();
        $player = $I->have(Player::class);

        $status = new ChargeStatus($player);

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

        $cycleEvent = new StatusCycleEvent($status, new Player(), $daedalus, $time);

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

        $status = new Status($player);

        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $player->addStatus($status);

        $cycleEvent = new StatusCycleEvent($status, $player, $daedalus, $time);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($actionPointBefore + 1, $player->getActionPoint());
    }

    public function testFireStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room]);

        $difficultyConfig = $daedalus->getGameConfig()->getDifficultyConfig();
        $difficultyConfig->setFirePlayerDamage([2 => 1]);

        $healthPointBefore = $player->getHealthPoint();

        $time = new DateTime();

        $status = new ChargeStatus($room);

        $status
            ->setName(StatusEnum::FIRE)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setCharge(1)
        ;

        $room->addStatus($status);

        $cycleEvent = new StatusCycleEvent($status, $room, $daedalus, $time);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($healthPointBefore - 2, $player->getHealthPoint());
    }
}
