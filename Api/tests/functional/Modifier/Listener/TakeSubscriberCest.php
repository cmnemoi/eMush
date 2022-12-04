<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Take;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;

class TakeSubscriberCest
{
    private Take $takeAction;

    public function _before(FunctionalTester $I)
    {
        $this->takeAction = $I->grabService(Take::class);
    }

    public function testTakeGearWithPlayerReach(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $this->takeAction->loadParameters($takeActionEntity, $player, $gameEquipment);
        $this->takeAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($player->getModifiers()->first()->getModifierConfig(), $modifierConfig);
    }

    public function testTakeGearCharged(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::HAZARDOUS);
        $I->haveInRepository($statusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::HAZARDOUS)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::SHOWER);

        $I->haveInRepository($statusConfig);
        $status = new ChargeStatus($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->takeAction->loadParameters($takeActionEntity, $player, $gameEquipment);
        $this->takeAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($player->getModifiers()->first()->getModifierConfig(), $modifierConfig);
        $I->assertEquals($player->getModifiers()->first()->getCharge(), $status);
    }

    public function testTakeGearIrrelevantCharged(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::HAZARDOUS);
        $I->haveInRepository($statusConfig);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setName(ActionEnum::REPAIR)
            ->setVisibility(VisibilityEnum::PUBLIC)
            ->setDischargeStrategy(ActionEnum::SHOWER);

        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->takeAction->loadParameters($takeActionEntity, $player, $gameEquipment);
        $this->takeAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 1);
        $I->assertEquals($room->getModifiers()->count(), 0);
        $I->assertEquals($player->getModifiers()->first()->getModifierConfig(), $modifierConfig);
        $I->assertEquals($player->getModifiers()->first()->getCharge(), null);
    }

    public function testTakeGearBroken(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::BROKEN);
        $I->haveInRepository($statusConfig);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->takeAction->loadParameters($takeActionEntity, $player, $gameEquipment);
        $this->takeAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }

    public function testTakeGearDaedalusReach(FunctionalTester $I)
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
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);

        $takeActionEntity = new Action();
        $takeActionEntity
            ->setName(ActionEnum::TAKE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($takeActionEntity);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::DAEDALUS)
            ->setMode(ModifierModeEnum::ADDITIVE)
        ;
        $I->haveInRepository($modifierConfig);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig]));
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
            'actions' => new ArrayCollection([$takeActionEntity]),
        ]);

        // Case of a game Equipment
        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        $statusConfig = new StatusConfig();
        $statusConfig->setName(EquipmentStatusEnum::BROKEN);
        $I->haveInRepository($statusConfig);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->takeAction->loadParameters($takeActionEntity, $player, $gameEquipment);
        $this->takeAction->execute();

        $I->assertEquals($room->getEquipments()->count(), 0);
        $I->assertEquals($player->getEquipments()->count(), 1);
        $I->assertEquals($player->getModifiers()->count(), 0);
        $I->assertEquals($room->getModifiers()->count(), 0);
    }
}
