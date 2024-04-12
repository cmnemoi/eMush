<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Hide;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class HideSubscriberCest
{
    private Hide $hideAction;

    public function _before(FunctionalTester $I)
    {
        $this->hideAction = $I->grabService(Hide::class);
    }

    public function testHideGearWithPlayerReach(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setName('hide_test')->setStatusName(EquipmentStatusEnum::HIDDEN);
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'maxItemInInventory' => 1,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $hideActionEntity = new Action();
        $hideActionEntity
            ->setActionName(ActionEnum::HIDE)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hideActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'soapShowerActionModifier']);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$hideActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $player->addEquipment($gameEquipment);

        $this->hideAction->loadParameters($hideActionEntity, $player, $gameEquipment);
        $this->hideAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testHideGearBroken(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setName('hide_test')->setStatusName(EquipmentStatusEnum::HIDDEN);
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'maxItemInInventory' => 1,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $hideActionEntity = new Action();
        $hideActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hideActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'soapShowerActionModifier']);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$hideActionEntity]),
        ]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->hideAction->loadParameters($hideActionEntity, $player, $gameEquipment);
        $this->hideAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testGearRoomReach(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig->setName('hide_test')->setStatusName(EquipmentStatusEnum::HIDDEN);
        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, [
            'maxItemInInventory' => 1,
            'statusConfigs' => new ArrayCollection([$statusConfig]),
        ]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        $player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $hideActionEntity = new Action();
        $hideActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setScope(ActionScopeEnum::CURRENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($hideActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, ['name' => 'soapShowerActionModifier']);
        $modifier = new GameModifier($room, $modifierConfig);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$hideActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->hideAction->loadParameters($hideActionEntity, $player, $gameEquipment);
        $this->hideAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 1);
    }
}
