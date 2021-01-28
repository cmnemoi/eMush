<?php

namespace functional\Equipment\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Player\Entity\Player;
use Mush\Room\Entity\Room;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EquipmentEventCest
{
    private EventDispatcherInterface $eventDispatcherService;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcherService = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDispatchEquipmentCreated(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'room' => $room]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        //Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC);
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getItems());

        //Case of a game Item
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC);
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $player->getItems());

        //Case of a game Item full inventory
        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC);
        $equipmentEvent->setPlayer($player);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(2, $room->getEquipments());
        $I->assertCount(1, $player->getItems());

        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'player' => $player->getId(),
            'log' => LogEnum::OBJECT_FELT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchEquipmentBroken(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        //Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_BROKEN);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(1, $room->getEquipments()->first()->getStatuses());
        $I->assertTrue($room->getEquipments()->first()->isBroken());
        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'log' => LogEnum::EQUIPMENT_BROKEN,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function testDispatchEquipmentDestroyed(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Room $room */
        $room = $I->have(Room::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig]);

        //Case of a game Equipment
        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setRoom($room)
        ;
        $I->haveInRepository($gameEquipment);

        $equipmentEvent = new EquipmentEvent($gameEquipment, VisibilityEnum::PUBLIC);

        $this->eventDispatcherService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->assertCount(0, $room->getEquipments());
        $I->seeInRepository(RoomLog::class, [
            'room' => $room->getId(),
            'log' => LogEnum::EQUIPMENT_DESTROYED,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
