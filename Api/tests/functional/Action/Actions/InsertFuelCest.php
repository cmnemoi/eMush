<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\InsertFuel;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Tool;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;

class InsertFuelCest
{
    private InsertFuel $insertFuelAction;

    public function _before(FunctionalTester $I)
    {
        $this->insertFuelAction = $I->grabService(InsertFuel::class);
    }

    public function testInsertFuel(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'fuel' => 5]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        $tankTool = new Tool();
        $tankTool->setActions(new ArrayCollection([$action]));
        $I->haveInRepository($tankTool);

        /** @var EquipmentConfig $tankConfig */
        $tankConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'mechanics' => new ArrayCollection([$tankTool])]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($tankConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $capsuleConfig */
        $capsuleConfig = $I->have(EquipmentConfig::class, ['isBreakable' => false, 'name' => ItemEnum::FUEL_CAPSULE]);

        $gameCapsule = new GameItem($player);
        $gameCapsule
            ->setEquipment($capsuleConfig)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameCapsule);

        $this->insertFuelAction->loadParameters($action, $player, $gameCapsule);

        $this->insertFuelAction->execute();

        $I->assertEquals(6, $daedalus->getFuel());
        $I->assertEmpty($player->getEquipments());
        $I->assertCount(1, $room->getEquipments());
    }

    public function testInsertFuelBrokenTank(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'fuel' => 5]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
        ]);
        /** @var User $user */
        $user = $I->have(User::class);
        $playerInfo = new PlayerInfo($player, $user, $characterConfig);

        $I->haveInRepository($playerInfo);
        $player->setPlayerInfo($playerInfo);
        $I->refreshEntities($player);

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::INSERT_FUEL)
            ->setScope(ActionScopeEnum::ROOM)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($action);

        $tankTool = new Tool();
        $tankTool->setActions(new ArrayCollection([$action]));
        $I->haveInRepository($tankTool);

        /** @var EquipmentConfig $tankConfig */
        $tankConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true, 'mechanics' => new ArrayCollection([$tankTool])]);

        $gameEquipment = new GameItem($room);
        $gameEquipment
            ->setEquipment($tankConfig)
            ->setName('some name')
        ;
        $I->haveInRepository($gameEquipment);

        /** @var EquipmentConfig $capsuleConfig */
        $capsuleConfig = $I->have(EquipmentConfig::class, ['isBreakable' => false, 'name' => ItemEnum::FUEL_CAPSULE]);

        $gameCapsule = new GameItem($player);
        $gameCapsule
            ->setEquipment($capsuleConfig)
            ->setName(ItemEnum::FUEL_CAPSULE)
        ;
        $I->haveInRepository($gameCapsule);

        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($statusConfig);
        $status = new Status($gameEquipment, $statusConfig);
        $I->haveInRepository($status);

        $this->insertFuelAction->loadParameters($action, $player, $gameCapsule);

        $I->assertFalse($this->insertFuelAction->isVisible());
    }
}
