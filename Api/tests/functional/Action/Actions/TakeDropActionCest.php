<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Actions\Hide;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class TakeDropActionCest
{
    private Take $takeAction;
    private Drop $dropAction;
    private Hide $hideAction;

    public function _before(FunctionalTester $I)
    {
        $this->takeAction = $I->grabService(Take::class);
        $this->dropAction = $I->grabService(Drop::class);
        $this->hideAction = $I->grabService(Hide::class);
    }

    public function testTakeDropItem(FunctionalTester $I)
    {
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionTake = new ActionConfig();
        $actionTake
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $actionDrop = new ActionConfig();
        $actionDrop
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionTake);
        $I->haveInRepository($actionDrop);

        /** @var ItemConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake, $actionDrop])]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower');
        $I->haveInRepository($gameItem);

        // first let's test take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertFalse($this->dropAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // drop ActionConfig
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->dropAction->isVisible());
        $I->assertFalse($this->takeAction->isVisible());
        $I->assertNull($this->dropAction->cannotExecuteReason());

        $this->dropAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::DROP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function TakeDropHeavyItem(FunctionalTester $I)
    {
        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig
            ->setStatusName(PlayerStatusEnum::BURDENED)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($burdenedStatusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$burdenedStatusConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionTake = new ActionConfig();
        $actionTake
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $actionDrop = new ActionConfig();
        $actionDrop
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionTake);
        $I->haveInRepository($actionDrop);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake, $actionDrop])]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower');
        $I->haveInRepository($gameItem);

        $heavyConfig = new StatusConfig();
        $heavyConfig
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($heavyConfig);
        $heavyStatus = new Status($gameItem, $heavyConfig);
        $I->haveInRepository($heavyStatus);

        // first let's test take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertFalse($this->dropAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(1, $player->getStatuses());
        $I->assertCount(1, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);

        // drop ActionConfig
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);
        $this->dropAction->loadParameters($actionDrop, $player, $gameItem);

        $I->assertTrue($this->dropAction->isVisible());
        $I->assertFalse($this->takeAction->isVisible());
        $I->assertNull($this->dropAction->cannotExecuteReason());

        $this->dropAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());
        $I->assertCount(1, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::DROP,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function TakeHiddenItem(FunctionalTester $I)
    {
        $hiddenConfig = new StatusConfig();
        $hiddenConfig
            ->setStatusName(EquipmentStatusEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hiddenConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$hiddenConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(6)
            ->setHealthPoint(2);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionTake = new ActionConfig();
        $actionTake
            ->setActionName(ActionEnum::TAKE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionTake);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionTake])]);

        $gameItem = new GameItem($room);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower');
        $I->haveInRepository($gameItem);

        $hiddenStatus = new Status($gameItem, $hiddenConfig);
        $hiddenStatus->setTarget($player);
        $I->haveInRepository($hiddenStatus);

        // Take action
        $this->takeAction->loadParameters($actionTake, $player, $gameItem);

        $I->assertTrue($this->takeAction->isVisible());
        $I->assertNull($this->takeAction->cannotExecuteReason());

        $this->takeAction->execute();

        $I->assertCount(1, $player->getEquipments());
        $I->assertCount(0, $room->getEquipments());
        $I->assertCount(0, $player->getStatuses());
        $I->assertCount(0, $gameItem->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $daedalusInfo,
            'playerInfo' => $player->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::TAKE,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
    }

    public function HideHeavyItemInInventory(FunctionalTester $I)
    {
        $hiddenStatusConfig = new StatusConfig();
        $hiddenStatusConfig
            ->setStatusName(EquipmentStatusEnum::HIDDEN)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hiddenStatusConfig);
        $burdenedStatusConfig = new StatusConfig();
        $burdenedStatusConfig
            ->setStatusName(PlayerStatusEnum::BURDENED)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($burdenedStatusConfig);

        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => GameConfigEnum::DEFAULT]);
        $gameConfig
            ->setStatusConfigs(new ArrayCollection([$hiddenStatusConfig, $burdenedStatusConfig]));
        $I->flushToDatabase();

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $localizationConfig = $I->grabEntityFromRepository(LocalizationConfig::class, ['name' => LanguageEnum::FRENCH]);

        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(2)
            ->setHealthPoint(6);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionHide = new ActionConfig();
        $actionHide
            ->setActionName(ActionEnum::HIDE)
            ->setRange(ActionRangeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($actionHide);

        /** @var EquipmentConfig $itemConfig */
        $itemConfig = $I->have(ItemConfig::class, ['actions' => new ArrayCollection([$actionHide])]);

        $gameItem = new GameItem($player);
        $gameItem
            ->setEquipment($itemConfig)
            ->setName('shower');
        $I->haveInRepository($gameItem);

        $burdenedStatus = new Status($player, $burdenedStatusConfig);
        $I->haveInRepository($burdenedStatus);
        $heavyConfig = new StatusConfig();

        $heavyConfig
            ->setStatusName(EquipmentStatusEnum::HEAVY)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($heavyConfig);
        $heavyStatus = new Status($gameItem, $heavyConfig);
        $I->haveInRepository($heavyStatus);

        // Take action
        $this->hideAction->loadParameters($actionHide, $player, $gameItem);

        $I->assertTrue($this->hideAction->isVisible());
        $I->assertNull($this->hideAction->cannotExecuteReason());

        $this->hideAction->execute();

        $I->assertCount(0, $player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
        $I->assertCount(2, $gameItem->getStatuses());
        $I->assertEquals($player, $gameItem->getStatusByName(EquipmentStatusEnum::HIDDEN)->getTarget());
        $I->assertCount(0, $player->getStatuses());
    }
}
