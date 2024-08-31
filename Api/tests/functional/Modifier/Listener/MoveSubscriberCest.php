<?php

namespace Mush\Tests\functional\Modifier\Listener;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\EquipmentEnum;
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
use Mush\Project\Entity\Project;
use Mush\Project\Entity\ProjectConfig;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\FunctionalTester;
use Mush\User\Entity\User;

class MoveSubscriberCest
{
    private Move $moveAction;
    private ActionConfig $moveActionConfig;
    private Place $currentRoom;
    private Place $destinationRoom;

    private Player $player;
    private Door $door;

    public function _before(FunctionalTester $I)
    {
        $this->moveAction = $I->grabService(Move::class);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->grabEntityFromRepository(GameConfig::class, ['name' => 'default']);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);

        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        $projectConfig = $I->grabEntityFromRepository(ProjectConfig::class, ['name' => ProjectName::ICARUS_LARGER_BAY]);
        $project = new Project($projectConfig, $daedalus);
        $I->haveInRepository($project);
        $daedalus->addProject($project);

        // Given 2 rooms joined by a door
        $this->currentRoom = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'currentPlace']);
        $this->destinationRoom = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'destinationPlace']);
        // @var Place $icarusBay
        $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ICARUS_BAY]);

        $this->moveActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::MOVE]);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->grabEntityFromRepository(
            EquipmentConfig::class,
            ['equipmentName' => EquipmentEnum::DOOR]
        );
        $this->door = new Door($this->destinationRoom);
        $this->door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->addRoom($this->destinationRoom)->addRoom($this->currentRoom);
        $I->haveInRepository($this->door);
        $this->currentRoom->addDoor($this->door);
        $this->destinationRoom->addDoor($this->door);
        $I->haveInRepository($this->currentRoom);
        $I->haveInRepository($this->destinationRoom);
        $I->haveInRepository($this->door);

        // Given a player in current room
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        $this->player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $this->currentRoom,
        ]);
        $this->player->setPlayerVariables($characterConfig);

        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($this->player, $user, $characterConfig);
        $I->haveInRepository($playerInfo);
        $this->player->setPlayerInfo($playerInfo);
        $I->haveInRepository($this->player);
    }

    public function testMoveWithStatusWithPlaceModifier(FunctionalTester $I)
    {
        // Given the player has a status with modifierConfig with ROOM reach
        $modifierConfigPlace = new VariableEventModifierConfig('testModifierShower2');
        $modifierConfigPlace
            ->setTargetEvent(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName();
        $I->haveInRepository($modifierConfigPlace);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$modifierConfigPlace]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);
        $statusPlayer = new Status($this->player, $statusConfig);
        $I->haveInRepository($statusPlayer);

        $modifierPlace = new GameModifier($this->currentRoom, $modifierConfigPlace);
        $modifierPlace->setModifierProvider($this->player);
        $I->haveInRepository($modifierPlace);

        $I->assertCount(1, $this->currentRoom->getPlayers());
        $I->assertCount(0, $this->destinationRoom->getPlayers());
        $I->assertCount(1, $this->currentRoom->getModifiers());
        $I->assertCount(0, $this->destinationRoom->getModifiers());

        $this->moveAction->loadParameters($this->moveActionConfig, $this->door, $this->player, $this->door);
        $I->assertNull($this->moveAction->cannotExecuteReason());
        $this->moveAction->execute();

        // let's check that every player and item is placed in the right place
        $I->assertCount(0, $this->currentRoom->getPlayers());
        $I->assertCount(1, $this->destinationRoom->getPlayers());
        $I->assertTrue($this->player->getStatuses()->contains($statusPlayer));

        // now check the modifiers
        $I->assertCount(0, $this->currentRoom->getModifiers());
        $I->assertCount(1, $this->destinationRoom->getModifiers());
    }

    public function testMoveWithEquipmentWithPlaceModifier(FunctionalTester $I)
    {
        // Given the player has a gear with an irrelevant reach
        $modifierConfigPlayerReach = new VariableEventModifierConfig('testModifierShower');
        $modifierConfigPlayerReach
            ->setTargetEvent(ActionEnum::MOVE->value)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLAYER)
            ->setMode(VariableModifierModeEnum::ADDITIVE);
        $I->haveInRepository($modifierConfigPlayerReach);
        $I->haveInRepository($this->player);

        $gear = new Gear();
        $gear
            ->setModifierConfigs(new ArrayCollection([$modifierConfigPlayerReach]))
            ->setName('gear_test');
        $I->haveInRepository($gear);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => 'test_1',
            'mechanics' => new ArrayCollection([$gear]),
        ]);
        $gameEquipment = new GameItem($this->player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);
        $this->player->addEquipment($gameEquipment);

        $modifierPlayer = new GameModifier($this->player, $modifierConfigPlayerReach);
        $modifierPlayer->setModifierProvider($gameEquipment);
        $I->haveInRepository($modifierPlayer);

        // Given the player has another gear with ROOM reach
        $modifierConfigPlaceReach = new VariableEventModifierConfig('testModifierShower2');
        $modifierConfigPlaceReach
            ->setTargetEvent(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName();
        $I->haveInRepository($modifierConfigPlaceReach);

        $gear2 = new Gear();
        $gear2
            ->setModifierConfigs(new ArrayCollection([$modifierConfigPlaceReach]))
            ->setName('gear_test_2');
        $I->haveInRepository($gear2);

        /** @var ItemConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(ItemConfig::class, [
            'name' => 'test_2',
            'mechanics' => new ArrayCollection([$gear2]),
        ]);
        $gameEquipment2 = $equipmentConfig2->createGameEquipment($this->player);
        $I->haveInRepository($gameEquipment2);
        $this->player->addEquipment($gameEquipment2);

        $modifierPlace = new GameModifier($this->currentRoom, $modifierConfigPlaceReach);
        $modifierPlace->setModifierProvider($gameEquipment2);
        $I->haveInRepository($modifierPlace);

        // Check our initialisation
        $I->assertCount(1, $this->currentRoom->getPlayers());
        $I->assertCount(0, $this->destinationRoom->getPlayers());
        $I->assertCount(1, $this->currentRoom->getModifiers());
        $I->assertCount(0, $this->destinationRoom->getModifiers());
        $I->assertCount(1, $this->player->getModifiers());
        $I->assertCount(2, $this->player->getEquipments());

        $this->moveAction->loadParameters($this->moveActionConfig, $this->door, $this->player, $this->door);
        $I->assertNull($this->moveAction->cannotExecuteReason());
        $this->moveAction->execute();

        // let's check that every player and item is placed in the right place
        $I->assertCount(0, $this->currentRoom->getPlayers());
        $I->assertCount(1, $this->destinationRoom->getPlayers());
        $I->assertCount(2, $this->player->getEquipments());

        // now check the modifiers
        $I->assertCount(0, $this->currentRoom->getModifiers());
        $I->assertCount(1, $this->destinationRoom->getModifiers());
        $I->assertCount(1, $this->player->getModifiers());
    }

    public function testMoveWithEquipmentHoldingCreatingModifierFromStatus(FunctionalTester $I)
    {
        // Given the player has an equipment
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => 'test_1',
        ]);
        $gameEquipment = new GameItem($this->player);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name');
        $I->haveInRepository($gameEquipment);

        $this->player->addEquipment($gameEquipment);

        // Given equipment has a status with modifierConfig with ROOM reach
        $modifierConfig = new VariableEventModifierConfig('testModifierShower2');
        $modifierConfig
            ->setTargetEvent(ActionVariableEnum::OUTPUT_QUANTITY)
            ->setTargetVariable(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setModifierRange(ModifierHolderClassEnum::PLACE)
            ->setMode(VariableModifierModeEnum::ADDITIVE)
            ->buildName();
        $I->haveInRepository($modifierConfig);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setStatusName(PlayerStatusEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$modifierConfig]))
            ->buildName(GameConfigEnum::TEST);
        $I->haveInRepository($statusConfig);

        $statusEquipment = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($statusEquipment);

        $modifier4 = new GameModifier($this->currentRoom, $modifierConfig);
        $modifier4->setModifierProvider($gameEquipment);
        $I->haveInRepository($modifier4);

        $I->assertCount(1, $this->currentRoom->getPlayers());
        $I->assertCount(0, $this->destinationRoom->getPlayers());

        $I->assertCount(1, $this->currentRoom->getModifiers());
        $I->assertCount(0, $this->destinationRoom->getModifiers());

        $this->moveAction->loadParameters($this->moveActionConfig, $this->door, $this->player, $this->door);
        $I->assertNull($this->moveAction->cannotExecuteReason());
        $this->moveAction->execute();

        // let's check that every player and item is placed in the right place
        $I->assertCount(0, $this->currentRoom->getPlayers());
        $I->assertCount(1, $this->destinationRoom->getPlayers());
        $I->assertCount(1, $this->player->getEquipments());
        $I->assertCount(1, $gameEquipment->getStatuses());

        // now check the modifiers
        $I->assertCount(0, $this->currentRoom->getModifiers());
        $I->assertCount(1, $this->destinationRoom->getModifiers());
        $I->assertCount(0, $this->player->getModifiers());
    }
}
