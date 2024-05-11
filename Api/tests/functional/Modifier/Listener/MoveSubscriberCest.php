<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionHolderEnum;
use Mush\Action\Enum\ActionRangeEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\GameConfigEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\VariableModifierModeEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MoveSubscriberCest
{
    private Move $moveAction;

    public function _before(FunctionalTester $I)
    {
        $this->moveAction = $I->grabService(Move::class);
    }

    public function testMoveWithPlaceModifiers(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
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
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, [
            'name' => 'door_test',
            'actionConfigs' => new ArrayCollection([$moveActionEntity]),
        ]);
        $door = new Door($room2);
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->addRoom($room2)->addRoom($room);
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

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        // first let create a gear with an irrelevant reach
        $modifierConfig1 = new VariableEventModifierConfig('testModifierShower');
        $modifierConfig1
            ->setTargetEvent(ActionEnum::MOVE->value)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $I->haveInRepository($modifierConfig1);
        $I->refreshEntities($player);
        $modifier = new GameModifier($player, $modifierConfig1);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfig1]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => 'test_1',
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        $gameEquipment = new GameItem($player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);
        $I->refreshEntities($player);
        $player->addEquipment($gameEquipment);
        $I->refreshEntities($player);

        // let's create a gear with room reach in player inventory
        $modifierConfig2 = new VariableEventModifierConfig('testModifierShower2');
        $modifierConfig2
            ->setTargetEvent(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName();
        $I->haveInRepository($modifierConfig2);
        $modifier2 = new GameModifier($room, $modifierConfig2);
        $I->haveInRepository($modifier2);

        $gear2 = new Gear();
        $gear2
            ->setModifierConfigs(new ArrayCollection([$modifierConfig2]))
            ->setName('gear_test_2');
        $I->haveInRepository($gear2);

        /** @var ItemConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, [
            'name' => 'test_2',
            'mechanics' => new ArrayCollection([$gear2]),
        ]);

        $gameEquipment2 = $equipmentConfig2->createGameItem($player);

        $I->haveInRepository($gameEquipment2);
        $I->refreshEntities($player);
        $player->addEquipment($gameEquipment2);
        $I->refreshEntities($player);

        // let's create a status with modifier with room reach on player
        $modifier3 = new GameModifier($room, $modifierConfig2);
        $I->haveInRepository($modifier3);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$modifierConfig2]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);
        $statusPlayer = new Status($player, $statusConfig);
        $I->haveInRepository($statusPlayer);

        // let's create a status with modifier with room reach on equipment2
        $modifier4 = new GameModifier($room, $modifierConfig2);
        $I->haveInRepository($modifier4);

        $I->haveInRepository($statusConfig);
        $statusEquipment = new Status($gameEquipment2, $statusConfig);
        $I->haveInRepository($statusEquipment);

        $I->assertCount(1, $room->getPlayers());
        $I->assertCount(0, $room2->getPlayers());

        $this->moveAction->loadParameters($moveActionEntity, $door, $player, $door);
        $I->assertNull($this->moveAction->cannotExecuteReason());
        $this->moveAction->execute();

        // let's check that every player and item is placed in the right place
        $I->assertCount(0, $room->getPlayers());
        $I->assertCount(1, $room2->getPlayers());
        $I->assertCount(2, $player->getEquipments());
        $I->assertCount(1, $player->getStatuses());
        $I->assertCount(0, $gameEquipment->getStatuses());
        $I->assertCount(1, $gameEquipment2->getStatuses());

        // now check the modifiers
        $I->assertCount(0, $room->getModifiers());
        $I->assertCount(3, $room2->getModifiers());
        $I->assertCount(1, $player->getModifiers());
    }
}
