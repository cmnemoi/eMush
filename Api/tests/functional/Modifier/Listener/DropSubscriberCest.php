<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Drop;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
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

class DropSubscriberCest
{
    private Drop $dropAction;

    public function _before(FunctionalTester $I)
    {
        $this->dropAction = $I->grabService(Drop::class);
    }

    public function testDropGearWithPlayerReach(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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

        $dropActionEntity = new ActionConfig();
        $dropActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($dropActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

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
            'actionConfigs' => new ArrayCollection([$dropActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $player->addEquipment($gameEquipment);

        $this->dropAction->loadParameters($dropActionEntity, $gameEquipment, $player, $gameEquipment);
        $this->dropAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDropGearBroken(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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

        $takeActionEntity = new ActionConfig();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actionConfigs' => new ArrayCollection([$takeActionEntity]),
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

        $this->dropAction->loadParameters($takeActionEntity, $gameEquipment, $player, $gameEquipment);
        $this->dropAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDropGearRoomReach(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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

        $takeActionEntity = new ActionConfig();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

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
            'actionConfigs' => new ArrayCollection([$takeActionEntity]),
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

        $this->dropAction->loadParameters($takeActionEntity, $gameEquipment, $player, $gameEquipment);
        $this->dropAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 0);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 1);
    }

    public function testDropGearWithOtherGear(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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

        $takeActionEntity = new ActionConfig();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);

        $modifier2 = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier2);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        $gear2 = new Gear();
        $gear2
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test_2');
        $I->haveInRepository($gear2);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actionConfigs' => new ArrayCollection([$takeActionEntity]),
            'name' => 'equipment_test_1',
        ]);

        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear2]),
            'actionConfigs' => new ArrayCollection([$takeActionEntity]),
            'name' => 'equipment_test_2',
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        // Case of a game Equipment
        $gameEquipment2 = new GameItem($player);
        $gameEquipment2
            ->setEquipment($equipmentConfig2)
            ->setName('some name');
        $I->haveInRepository($gameEquipment2);

        $player->addEquipment($gameEquipment)->addEquipment($gameEquipment2);

        $this->dropAction->loadParameters($takeActionEntity, $gameEquipment, $player, $gameEquipment);
        $this->dropAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($player->getModifiers()->first(), $modifier2);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testDropOneOfTwoSameGear(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['maxItemInInventory' => 1]);

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

        $takeActionEntity = new ActionConfig();
        $takeActionEntity
            ->setActionName(ActionEnum::DROP)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'soapShowerActionModifier',
        ]);

        $modifier = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier);
        $modifier2 = new GameModifier($player, $modifierConfig);
        $I->haveInRepository($modifier2);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actionConfigs' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        // Case of a game Equipment
        $gameEquipment2 = new GameItem($player);
        $gameEquipment2
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment2);

        $this->dropAction->loadParameters($takeActionEntity, $gameEquipment, $player, $gameEquipment);
        $this->dropAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 1);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }
}
