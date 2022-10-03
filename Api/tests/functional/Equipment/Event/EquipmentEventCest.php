<?php

namespace Mush\Tests\Equipment\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Game\Service\EventServiceInterface;

class EquipmentEventCest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I) : void
    {
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testHeavyStatusOverflowingInventory(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 0]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'characterConfig' => $characterConfig]);

        $heavyStatusConfig = new StatusConfig();
        $heavyStatusConfig->setName(EquipmentStatusEnum::HEAVY)->setGameConfig($gameConfig);
        $I->haveInRepository($heavyStatusConfig);

        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig->setName(PlayerStatusEnum::BURDENED)->setGameConfig($gameConfig);
        $I->haveInRepository($burdenedStatusConfig);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'name' => 'equipment_name',
            'initStatus' => new ArrayCollection([$heavyStatusConfig]),
        ]);

        $equipment = $itemConfig->createGameItem();
        $equipment->setHolder($player);
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEmpty($player->getEquipments());
        $I->assertEquals(1, $room->getEquipments()->count());
        $I->assertEmpty($player->getStatuses());
        $I->assertEquals(1, $room->getEquipments()->first()->getStatuses()->count());
    }
}
