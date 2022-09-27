<?php

namespace functional\RoomLog\Listener;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\PlantLogEnum;

class EquipmentSubscriberCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testCreateNewFruit(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'name' => 'equipment_name']);

        $equipment = new GameItem();
        $equipment
            ->setName($equipmentConfig->getName())
            ->setEquipment($equipmentConfig)
            ->setHolder($room);
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            EventEnum::PLANT_PRODUCTION,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getEquipments());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'log' => PlantLogEnum::PLANT_NEW_FRUIT,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }
}
