<?php

namespace Mush\Tests\Status\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
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
        //Cycle Increment
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
    }

    public function testLieDownStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

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
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
            'propagatingFireRate' => 100,
            'hullFireDamageRate' => 100, ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

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
        $moralPointBefore = $player->getMoralPoint();
        $hullPointBefore = $daedalus->getHull();

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
        $I->assertEquals($moralPointBefore, $player->getMoralPoint());
        $I->assertEquals($hullPointBefore - 2, $daedalus->getHull());

        $I->assertEquals(StatusEnum::FIRE, $room2->getStatuses()->first()->getName());
        $I->assertEquals(0, $room2->getStatuses()->first()->getCharge());
    }
}
