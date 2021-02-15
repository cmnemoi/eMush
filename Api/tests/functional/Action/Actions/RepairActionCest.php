<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;

class RepairActionCest
{
    private Repair $repairAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
    }

    public function testRepair(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2]);
        $action = new Action();

        $action
            ->setName(ActionEnum::REPAIR)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setTypes([ModifierScopeEnum::ACTION_TECHNICIAN])
        ;

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['breakableRate' => 25]);

        $gameEquipment = new GameItem();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setPlace($room)
        ;

        $actionParameters = new ActionParameters();
        $actionParameters->setItem($gameEquipment);

        $this->repairAction->loadParameters($action, $player, $actionParameters);

        $I->assertFalse($this->repairAction->canExecute());

        $status = new Status($gameEquipment);
        $status
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;

        $I->assertEquals(25, $this->repairAction->getSuccessRate());
        $I->assertTrue($this->repairAction->canExecute());

        $wrench = $this->createWrenchItem();
        $player->addItem($wrench);

        $I->assertEquals(37, $this->repairAction->getSuccessRate());
    }

    private function createWrenchItem(): GameItem
    {
        $modifier = new Modifier();
        $modifier
            ->setTarget(ModifierTargetEnum::PERCENTAGE)
            ->setDelta(1.5)
            ->setScope(ModifierScopeEnum::ACTION_TECHNICIAN)
            ->setReach(ReachEnum::INVENTORY)
        ;

        $wrenchGear = new Gear();

        $wrenchGear->setModifier(new arrayCollection([$modifier]));

        $wrench = new ItemConfig();
        $wrench
            ->setName(GearItemEnum::ADJUSTABLE_WRENCH)
            ->setIsHeavy(false)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(false)
            ->setIsFireBreakable(false)
            ->setMechanics(new ArrayCollection([$wrenchGear]))
        ;

        $gameWrench = new GameItem();
        $gameWrench->setEquipment($wrench);

        return $gameWrench;
    }
}
