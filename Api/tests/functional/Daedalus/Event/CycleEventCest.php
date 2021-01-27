<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Daedalus\Event\DaedalusCycleSubscriber;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Plant;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Enum\DoorEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\StatusEnum;

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
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'actionPoint' => 2]);
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class);

        $gameEquipment = new GameEquipment();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
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
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var CharacterConfig $characterConfig2 */
        $characterConfig2 = $I->have(CharacterConfig::class, ['name' => CharacterEnum::ANDIE]);
        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'room' => $room, 'characterConfig' => $characterConfig]
        );
        $I->have(
            Player::class,
            ['daedalus' => $daedalus, 'room' => $room, 'characterConfig' => $characterConfig2]
        );

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertEquals(0, $daedalus->getOxygen());
        $I->assertCount(1, $daedalus->getPlayers()->getPlayerAlive());
        $I->assertEquals(9, $daedalus->getPlayers()->getPlayerAlive()->first()->getMoralPoint());
    }

    public function testStartingFire(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
                                        'startingFireRate' => 100,
                                        'equipmentFireBreakRate' => 100,
                                        'propagatingFireRate' => 100,
                                    ]);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig, 'daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Room $room */
        $room2 = $I->have(Room::class, ['daedalus' => $daedalus]);

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

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isFireBreakable' => true, 'isFireDestroyable' => false, 'gameConfig' => $gameConfig]);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(EquipmentConfig::class, ['isFireBreakable' => false, 'isFireDestroyable' => true, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);

        $gameEquipment2 = new GameEquipment();

        $gameEquipment2
            ->setEquipment($equipmentConfig2)
            ->setName('some other name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment2);

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(1, $room->getStatuses());
        $I->assertEquals(StatusEnum::FIRE, $room->getStatuses()->first()->getName());
        $I->assertEquals(1, $room->getStatuses()->first()->getCharge());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertEquals(EquipmentStatusEnum::BROKEN, $room->getEquipments()->first()->getStatuses()->first()->getName());
        $I->assertEquals('some name', $room->getEquipments()->first()->getName());

        $I->assertEquals(StatusEnum::FIRE, $room->getStatuses()->first()->getName());
        $I->assertEquals(1, $room2->getStatuses()->first()->getCharge());
    }

    public function testCycleEquipmentBreak(FunctionalTester $I)
    {
        /** @var DaedalusConfig $gameConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class, [
                                        'equipmentBreakRate' => 100,
                                        'doorBreakRate' => 100,
                                    ]);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig, 'daedalusConfig' => $daedalusConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Room $room */
        $room2 = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['breakableRate' => 25, 'gameConfig' => $gameConfig]);

        $doorConfig
            ->setGameConfig($daedalus->getGameConfig())
            ->setIsFireBreakable(false)
            ->setIsFireDestroyable(false);

        $door = new Door();
        $door
            ->setName(DoorEnum::FRONT_CORRIDOR_BRIDGE)
            ->setEquipment($doorConfig)
        ;

        $room->addDoor($door);
        $room2->addDoor($door);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['breakableRate' => 0, 'gameConfig' => $gameConfig]);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(EquipmentConfig::class, ['breakableRate' => 25, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);

        $gameEquipment2 = new GameEquipment();

        $gameEquipment2
            ->setEquipment($equipmentConfig2)
            ->setName('some other name')
            ->setRoom($room2)
        ;
        $I->haveInRepository($gameEquipment2);

        $time = new DateTime();

        $cycleEvent = new DaedalusCycleEvent($daedalus, $time);

        $this->cycleSubscriber->onNewCycle($cycleEvent);

        $I->assertCount(0, $room->getStatuses());
        $I->assertTrue($room2->getEquipments()->first()->isBroken());
        $I->assertFalse($room->getEquipments()->first()->isBroken());

        $I->assertTrue($room2->getDoors()->first()->isBroken());
    }
}
