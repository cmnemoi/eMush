<?php

namespace Mush\Tests\Room\Event;

use App\Tests\FunctionalTester;
use DateTime;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\DifficultyConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\Room\Event\RoomEvent;
use Mush\Room\Event\RoomSubscriber;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\StatusEnum;

class RoomEventCest
{
    private RoomSubscriber $roomSubscriber;

    public function _before(FunctionalTester $I)
    {
        $this->roomSubscriber = $I->grabService(RoomSubscriber::class);
    }

    // tests
    public function testNewFire(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var Room $room */
        $room = $I->have(Room::class);

        $roomEvent = new RoomEvent($room, $time);

        $this->roomSubscriber->onStartingFire($roomEvent);

        $I->assertEquals(1, $room->getStatuses()->count());

        /** @var Status $fireStatus */
        $fireStatus = $room->getStatuses()->first();

        $I->assertEquals($room, $fireStatus->getOwner());
        $I->assertEquals(StatusEnum::FIRE, $fireStatus->getName());
    }

    // tests
    public function testTremor(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'healthPoint' => 10]);

        $roomEvent = new RoomEvent($room, $time);
        $this->roomSubscriber->onTremor($roomEvent);

        $I->assertEquals(8, $player->getHealthPoint());
        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'log' => LogEnum::TREMOR_GRAVITY,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    // tests
    public function testElectricArc(FunctionalTester $I)
    {
        $time = new DateTime();
        /** @var DifficultyConfig $difficultyConfig */
        $difficultyConfig = $I->have(DifficultyConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['difficultyConfig' => $difficultyConfig]);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room, 'healthPoint' => 10]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['breakableRate' => 12, 'gameConfig' => $gameConfig]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);

        $roomEvent = new RoomEvent($room, $time);
        $this->roomSubscriber->onElectricArc($roomEvent);

        $I->assertEquals(7, $player->getHealthPoint());
        $I->assertTrue($gameEquipment->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'log' => LogEnum::ELECTRIC_ARC,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
