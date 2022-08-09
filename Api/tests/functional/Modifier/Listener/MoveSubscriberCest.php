<?php

namespace functional\Modifier\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Move;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Game\Entity\GameConfig;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

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
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Place $room2 */
        $room2 = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => RoomEnum::ALPHA_BAY]);

        $actionCost = new ActionCost();
        $I->haveInRepository($actionCost);
        $moveActionEntity = new Action();
        $moveActionEntity
            ->setName(ActionEnum::MOVE)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($moveActionEntity);

        /** @var EquipmentConfig $doorConfig */
        $doorConfig = $I->have(EquipmentConfig::class, ['gameConfig' => $gameConfig, 'actions' => new ArrayCollection([$moveActionEntity])]);
        $door = new Door();
        $door
            ->setName('door name')
            ->setEquipment($doorConfig)
            ->setHolder($room2)
        ;
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
            'characterConfig' => $characterConfig,
        ]);

        // first let create a gear with an irrelevant reach
        $modifierConfig1 = new ModifierConfig();
        $modifierConfig1
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLAYER)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig1);
        $I->refreshEntities($player);
        $modifier = new Modifier($player, $modifierConfig1);
        $I->haveInRepository($modifier);

        $gear = new Gear();
        $gear->setModifierConfigs(new ArrayCollection([$modifierConfig1]));
        $I->haveInRepository($gear);
        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear]),
        ]);

        $gameEquipment = new GameItem();
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment);
        $I->refreshEntities($player);
        $player->addEquipment($gameEquipment);
        $I->refreshEntities($player);

        // lets create a gear with room reach in player inventory
        $modifierConfig2 = new ModifierConfig();
        $modifierConfig2
            ->setScope(ActionEnum::SHOWER)
            ->setTarget(PlayerVariableEnum::MORAL_POINT)
            ->setDelta(-1)
            ->setReach(ModifierReachEnum::PLACE)
            ->setMode(ModifierModeEnum::ADDITIVE)
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($modifierConfig2);
        $modifier2 = new Modifier($room, $modifierConfig2);
        $I->haveInRepository($modifier2);

        $gear2 = new Gear();
        $gear2->setModifierConfigs(new ArrayCollection([$modifierConfig2]));
        $I->haveInRepository($gear2);
        /** @var EquipmentConfig $equipmentConfig2 */
        $equipmentConfig2 = $I->have(EquipmentConfig::class, [
            'gameConfig' => $gameConfig,
            'mechanics' => new ArrayCollection([$gear2]),
        ]);

        $gameEquipment2 = new GameItem();
        $gameEquipment2
            ->setEquipment($equipmentConfig2)
            ->setName('some name')
            ->setHolder($player)
        ;
        $I->haveInRepository($gameEquipment2);
        $I->refreshEntities($player);
        $player->addEquipment($gameEquipment2);
        $I->refreshEntities($player);

        // lets create a status with modifier with room reach on player
        $modifier3 = new Modifier($room, $modifierConfig2);
        $I->haveInRepository($modifier3);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(PlayerStatusEnum::MUSH)
            ->setModifierConfigs(new ArrayCollection([$modifierConfig2]))
            ->setGameConfig($gameConfig)
        ;
        $I->haveInRepository($statusConfig);
        $statusPlayer = new Status($player, $statusConfig);
        $I->haveInRepository($statusPlayer);

        // lets create a status with modifier with room reach on equipment2
        $modifier4 = new Modifier($room, $modifierConfig2);
        $I->haveInRepository($modifier4);

        $I->haveInRepository($statusConfig);
        $statusEquipment = new Status($gameEquipment2, $statusConfig);
        $I->haveInRepository($statusEquipment);

        $I->assertCount(1, $room->getPlayers());
        $I->assertCount(0, $room2->getPlayers());

        $this->moveAction->loadParameters($moveActionEntity, $player, $door);
        $this->moveAction->execute();

        // lets check that every player and item is placed in the right place
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
