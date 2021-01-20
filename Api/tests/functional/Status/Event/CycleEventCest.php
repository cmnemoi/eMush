<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Event\CycleEvent;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;
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

    public function testFireStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 100, ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Room $room2 */
        $room2 = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room]);

        /** @var EquipmentConfig $equipmentConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['isFireBreakable' => false, 'isFireDestroyable' => false, 'gameConfig' => $gameConfig]);

        $doorConfig
            ->setGameConfig($daedalus->getGameConfig())
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false);

        $door = new Door();
        $door
             ->setName('door name')
             ->setEquipment($doorConfig)
        ;

        $room->addDoor($door);
        $room2->addDoor($door);

        $healthPointBefore = $player->getHealthPoint();
        $hullPointBefore = $daedalus->getHull();

        $time = new DateTime();

        $cycleEvent = new CycleEvent($daedalus, $time);

        $status = new ChargeStatus();

        $status
             ->setName(StatusEnum::FIRE)
             ->setVisibility(VisibilityEnum::PUBLIC)
             ->setRoom($room)
             ->setCharge(1)
         ;

        $cycleEvent->setStatus($status);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals($healthPointBefore - 2, $player->getHealthPoint());
        $I->assertEquals($hullPointBefore - 2, $daedalus->getHull());

        $I->assertEquals(StatusEnum::FIRE, $room2->getStatuses()->first()->getName());
        $I->assertEquals(0, $room2->getStatuses()->first()->getCharge());
    }
}
