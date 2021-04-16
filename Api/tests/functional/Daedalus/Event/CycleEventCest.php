<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusCycleSubscriber;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class CycleEventCest
{
    private DaedalusCycleSubscriber $cycleSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->cycleSubscriber = $I->grabService(DaedalusCycleSubscriber::class);
    }

    public function testLieDownStatusCycleSubscriber(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'healthPoint' => 99,
            'characterConfig' => $characterConfig,
        ]);
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        $gameEquipment = new GameEquipment();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setPlace($room)
        ;

        $I->haveInRepository($gameEquipment);

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $status = new Status($player);

        $status
            ->setName(PlayerStatusEnum::LYING_DOWN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setTarget($gameEquipment)
        ;

        $player->addStatus($status);

        $I->haveInRepository($status);
        $I->refreshEntities($player, $daedalus, $gameEquipment);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals(4, $player->getActionPoint());
    }

    public function testOxygenCycleSubscriber(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'oxygen' => 1]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);

        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig, 'healthPoint' => 99]
        );
        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig2, 'healthPoint' => 99]
        );

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals(0, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
    }
}
