<?php

namespace functional\Action\Actions;

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
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;

class MovementPointConversionCest
{
    private Move $moveAction;

    public function _before(FunctionalTester $I)
    {
        $this->moveAction = $I->grabService(Move::class);
    }

    public function testBasicConversion(FunctionalTester $I)
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
        $actionCost->setActionPointCost(null)->setMovementPointCost(1);
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
            'movementPoint' => 0,
            'actionPoint' => 2,
        ]);

        $this->moveAction->loadParameters($moveActionEntity, $player, $door);

        $I->assertEquals(1, $this->moveAction->getMovementPointCost());
        $I->assertEquals(1, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 2);
        $I->assertEquals($player->getMovementPoint(), 0);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 1);
        $I->assertEquals($player->getMovementPoint(), 1);
    }

    public function testConversionWithIncreasedMovementCost(FunctionalTester $I)
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
        $actionCost->setActionPointCost(null)->setMovementPointCost(2);
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
            'movementPoint' => 1,
            'actionPoint' => 10,
        ]);

        $this->moveAction->loadParameters($moveActionEntity, $player, $door);

        $I->assertEquals(2, $this->moveAction->getMovementPointCost());
        $I->assertEquals(1, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 10);
        $I->assertEquals($player->getMovementPoint(), 1);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 9);
        $I->assertEquals($player->getMovementPoint(), 1);
    }

    public function testSeveralConversionRequired(FunctionalTester $I)
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
        $actionCost->setActionPointCost(null)->setMovementPointCost(5);
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
            'movementPoint' => 1,
            'actionPoint' => 10,
        ]);

        $this->moveAction->loadParameters($moveActionEntity, $player, $door);

        $I->assertEquals(5, $this->moveAction->getMovementPointCost());
        $I->assertEquals(2, $this->moveAction->getActionPointCost());
        $I->assertEquals($player->getActionPoint(), 10);
        $I->assertEquals($player->getMovementPoint(), 1);

        $this->moveAction->execute();

        $I->assertEquals($player->getActionPoint(), 8);
        $I->assertEquals($player->getMovementPoint(), 0);
    }
}
