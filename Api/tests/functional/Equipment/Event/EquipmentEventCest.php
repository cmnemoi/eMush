<?php

namespace Mush\Tests\Equipment\Event;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EquipmentEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testHeavyStatusOverflowingInventory(FunctionalTester $I)
    {
        $heavyStatusConfig = new StatusConfig();
        $heavyStatusConfig->setName(EquipmentStatusEnum::HEAVY);
        $I->haveInRepository($heavyStatusConfig);
        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig->setName(PlayerStatusEnum::BURDENED);
        $I->haveInRepository($burdenedStatusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class,
            ['statusConfigs' => new ArrayCollection([$burdenedStatusConfig, $heavyStatusConfig])]
        );

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class, ['maxItemInInventory' => 0]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, [
            'gameConfig' => $gameConfig,
            'name' => 'equipment_name',
            'initStatus' => new ArrayCollection([$heavyStatusConfig]),
        ]);

        $equipment = $itemConfig->createGameItem($player);
        $I->haveInRepository($equipment);

        $equipmentEvent = new EquipmentEvent(
            $equipment,
            true,
            VisibilityEnum::PUBLIC,
            ActionEnum::COFFEE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $I->assertEmpty($player->getEquipments());
        $I->assertEquals(1, $room->getEquipments()->count());
        $I->assertEmpty($player->getStatuses());
        $I->assertEquals(1, $room->getEquipments()->first()->getStatuses()->count());
    }
}
