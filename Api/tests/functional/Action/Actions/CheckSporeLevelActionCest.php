<?php

namespace Mush\Tests\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\CheckSporeLevel;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\ActionOutputEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\PlayerStatusEnum;

class CheckSporeLevelActionCest
{
    private CheckSporeLevel $checkSporeLevel;

    public function _before(FunctionalTester $I)
    {
        $this->checkSporeLevel = $I->grabService(CheckSporeLevel::class);
    }

    public function testCheckSporeLevel(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus, 'name' => 'roomName']);

        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, [
            'daedalus' => $daedalus,
            'place' => $room,
            'actionPoint' => 2,
            'characterConfig' => $characterConfig,
        ]);

        $sporeStatusConfig = new ChargeStatusConfig();
        $sporeStatusConfig
            ->setName(PlayerStatusEnum::SPORES)
            ->setVisibility(VisibilityEnum::MUSH)
            ->setChargeVisibility(VisibilityEnum::MUSH)
            ->setGameConfig($gameConfig)
        ;

        $I->haveInRepository($sporeStatusConfig);

        $sporeStatus = new ChargeStatus($player, $sporeStatusConfig);
        $sporeStatus
            ->setCharge(2)
        ;

        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost(0)
        ;
        $I->haveInRepository($actionCost);

        $action = new Action();
        $action
            ->setName(ActionEnum::CHECK_SPORE_LEVEL)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setActionCost($actionCost)
            ->setVisibility(ActionOutputEnum::SUCCESS, VisibilityEnum::PRIVATE)
        ;
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::MYCOSCAN,
            'actions' => new ArrayCollection([$action]),
        ]);

        $gameEquipment = new GameEquipment();
        $gameEquipment
            ->setName(EquipmentEnum::MYCOSCAN)
            ->setEquipment($equipmentConfig)
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $this->checkSporeLevel->loadParameters($action, $player, $gameEquipment);

        $I->assertTrue($this->checkSporeLevel->isVisible());

        $this->checkSporeLevel->execute();

        $I->assertEquals(2, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room,
            'player' => $player,
            'visibility' => VisibilityEnum::PRIVATE,
            'log' => ActionLogEnum::CHECK_SPORE_LEVEL,
        ]);
    }
}
