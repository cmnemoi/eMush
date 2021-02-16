<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;

class ShowerActionCest
{
    private Shower $showerAction;

    public function _before(FunctionalTester $I)
    {
        $this->showerAction = $I->grabService(Shower::class);
    }

    public function testMushShower(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus,
                                            'place' => $room,
                                            'actionPoint' => 2,
                                            'healthPoint' => 6, ]);

        $mushStatus = new Status($player);
        $mushStatus
            ->setName(PlayerStatusEnum::MUSH)
            ->setVisibility(VisibilityEnum::MUSH)
        ;

        $actionCost = new ActionCost();

        $actionCost
            ->setActionPointCost(2)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);

        $action = new Action();
        $action
            ->setName(ActionEnum::SHOWER)
            ->setDirtyRate(0)
            ->setScope(ActionScopeEnum::CURRENT)
            ->setInjuryRate(0)
            ->setActionCost($actionCost)
        ;
        $I->haveInRepository($actionCost);
        $I->haveInRepository($action);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['actions' => new ArrayCollection([$action])]);

        $gameEquipment = new GameEquipment();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('shower')
            ->setPlace($room)
        ;
        $I->haveInRepository($gameEquipment);

        $soap = $this->createSoapItem($I);

        $player->addItem($soap);

        $actionParameters = new ActionParameters();
        $actionParameters->setEquipment($gameEquipment);

        $this->showerAction->loadParameters($action, $player, $actionParameters);

        $I->assertTrue($this->showerAction->canExecute());

        $this->showerAction->execute();

        $I->assertEquals(3, $player->getHealthPoint());

        $I->assertEquals(1, $player->getActionPoint());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getId(),
            'player' => $player->getId(),
            'log' => ActionLogEnum::SHOWER_MUSH,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);

        //@TODO test skill water resistance
    }

    private function createSoapItem(FunctionalTester $I): GameItem
    {
        $modifier = new Modifier();
        $modifier
            ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ->setDelta(-1)
            ->setScope(ActionEnum::SHOWER)
            ->setReach(ReachEnum::INVENTORY)
        ;

        $soapGear = new Gear();

        $soapGear->setModifier(new arrayCollection([$modifier]));

        $soap = new ItemConfig();
        $soap
            ->setName(GearItemEnum::SOAP)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$soapGear]))
        ;

        $gameSoap = new GameItem();
        $gameSoap
            ->setName(GearItemEnum::SOAP)
            ->setEquipment($soap)
        ;

        $I->haveInRepository($modifier);
        $I->haveInRepository($soapGear);
        $I->haveInRepository($soap);
        $I->haveInRepository($gameSoap);

        return $gameSoap;
    }
}
