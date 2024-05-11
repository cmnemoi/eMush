<?php

namespace Mush\Tests\functional\Action\Actions;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MovementPointConversionCest
{
    private Move $moveAction;

    public function _before(FunctionalTester $I)
    {
        $this->moveAction = $I->grabService(Move::class);
    }

    public function testBasicConversion(FunctionalTester $I)
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

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $convertActionEntity = new ActionConfig();
        $convertActionEntity
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $I->haveInRepository($convertActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actionConfigs' => new ArrayCollection([$moveActionEntity])]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

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
            ->setMovementPoint(0);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->moveAction->loadParameters(
            actionConfig: $moveActionEntity,
            actionProvider: $door,
            player: $player,
            target: $door
        );

        $I->assertEquals(1, $this->moveAction->getMovementPointCost());
        $I->assertEquals(0, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 2);
        $I->assertEquals($player->getMovementPoint(), 0);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 1);
        $I->assertEquals($player->getMovementPoint(), 2);
    }

    public function testConversionWithIncreasedMovementCost(FunctionalTester $I)
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

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(2)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $convertActionEntity = new ActionConfig();
        $convertActionEntity
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $I->haveInRepository($convertActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actionConfigs' => new ArrayCollection([$moveActionEntity])]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
            ->setMovementPoint(1);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->moveAction->loadParameters(
            actionConfig: $moveActionEntity,
            actionProvider: $door,
            player: $player,
            target: $door
        );

        $I->assertNull($this->moveAction->cannotExecuteReason());
        $I->assertEquals(2, $this->moveAction->getMovementPointCost());
        $I->assertEquals(0, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 10);
        $I->assertEquals($player->getMovementPoint(), 1);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 9);
        $I->assertEquals($player->getMovementPoint(), 2);
    }

    public function testSeveralConversionRequired(FunctionalTester $I)
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

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(5)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $convertActionEntity = new ActionConfig();
        $convertActionEntity
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $I->haveInRepository($convertActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actionConfigs' => new ArrayCollection([$moveActionEntity])]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(10)
            ->setMovementPoint(1);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->moveAction->loadParameters(
            actionConfig: $moveActionEntity,
            actionProvider: $door,
            player: $player,
            target: $door
        );

        $I->assertEquals(5, $this->moveAction->getMovementPointCost());
        $I->assertEquals(0, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 10);
        $I->assertEquals($player->getMovementPoint(), 1);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 8);
        $I->assertEquals($player->getMovementPoint(), 2);
    }

    public function testConversionImpossible(FunctionalTester $I)
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

        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);

        /** @var Place $icarusBay */
        $icarusBay = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $moveActionEntity = new ActionConfig();
        $moveActionEntity
            ->setActionName(ActionEnum::MOVE)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::EQUIPMENT)
            ->setMovementCost(1)
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        $convertActionEntity = new ActionConfig();
        $convertActionEntity
            ->setActionName(ActionEnum::CONVERT_ACTION_TO_MOVEMENT)
            ->setRange(ActionRangeEnum::SELF)
            ->setDisplayHolder(ActionHolderEnum::PLAYER)
            ->buildName(GameConfigEnum::TEST);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => 1, 'min_value' => 0, 'max_value' => null], PlayerVariableEnum::ACTION_POINT);
        $convertActionEntity->getGameVariables()->setValuesByName(['value' => -3, 'min_value' => null, 'max_value' => 0], PlayerVariableEnum::MOVEMENT_POINT);
        $I->haveInRepository($convertActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig);
        $I->haveInRepository($door);
        $room->addDoor($door);
        $room2->addDoor($door);
        $I->refreshEntities($room, $room2, $door);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);

        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
        ]);
        $player->setPlayerVariables($characterConfig);
        $player
            ->setActionPoint(0)
            ->setMovementPoint(0);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $this->moveAction->loadParameters(
            actionConfig: $moveActionEntity,
            actionProvider: $door,
            player: $player,
            target: $door
        );

        $I->assertNotNull($this->moveAction->cannotExecuteReason());
    }
}
