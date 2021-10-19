<?php

namespace functional\Action\Actions;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Disassemble;
use Mush\Action\Actions\Repair;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionCost;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Entity\CharacterConfig;
use Mush\Game\Entity\GameConfig;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;

class AttemptActionChangeCest
{
    private Repair $repairAction;
    private Disassemble $disassembleAction;

    public function _before(FunctionalTester $I)
    {
        $this->repairAction = $I->grabService(Repair::class);
        $this->disassembleAction = $I->grabService(Disassemble::class);
    }

    public function testChangeAttemptAction(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2, 'characterConfig' => $characterConfig]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);
        $I->haveInRepository($actionCost);

        $actionRepair = new Action();
        $actionRepair
            ->setName(ActionEnum::REPAIR)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(0)
            ->setActionCost($actionCost)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $I->haveInRepository($actionRepair);

        $actionDisassemble = new Action();
        $actionDisassemble
            ->setName(ActionEnum::DISASSEMBLE)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(0)
            ->setActionCost($actionCost)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $I->haveInRepository($actionDisassemble);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActions(new ArrayCollection([$actionDisassemble, $actionRepair]));

        $gameEquipment = new GameItem();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $status = new Status($gameEquipment);
        $status
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($status);

        $this->repairAction->loadParameters($actionRepair, $player, $gameEquipment);

        //Execute repair
        $this->repairAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->repairAction->loadParameters($actionRepair, $player, $gameEquipment);
        //Execute repair a second time
        $this->repairAction->execute();
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $player, $gameEquipment);
        //Now execute the other action
        $this->disassembleAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::DISASSEMBLE, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $player, $gameEquipment);
        $this->disassembleAction->execute();
        $I->assertEquals(ActionEnum::DISASSEMBLE, $player->getStatuses()->first()->getAction());
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
    }

    public function testNormalizeAnotherAction(FunctionalTester $I)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class);
        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig]);
        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);
        /** @var CharacterConfig $characterConfig */
        $characterConfig = $I->have(CharacterConfig::class);
        /** @var Player $player */
        $player = $I->have(Player::class, ['daedalus' => $daedalus, 'place' => $room, 'actionPoint' => 2, 'characterConfig' => $characterConfig]);

        $actionCost = new ActionCost();
        $actionCost->setActionPointCost(0)
            ->setMovementPointCost(0)
            ->setMoralPointCost(0);
        $I->haveInRepository($actionCost);

        $actionRepair = new Action();
        $actionRepair
            ->setName(ActionEnum::REPAIR)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(0)
            ->setActionCost($actionCost)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $I->haveInRepository($actionRepair);

        $actionDisassemble = new Action();
        $actionDisassemble
            ->setName(ActionEnum::DISASSEMBLE)
            ->setDirtyRate(0)
            ->setInjuryRate(0)
            ->setSuccessRate(75)
            ->setActionCost($actionCost)
            ->setScope(ActionScopeEnum::CURRENT)
        ;
        $I->haveInRepository($actionDisassemble);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->have(EquipmentConfig::class, ['isBreakable' => true]);

        $equipmentConfig->setActions(new ArrayCollection([$actionDisassemble, $actionRepair]));

        $gameEquipment = new GameItem();

        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName('some name')
            ->setHolder($room)
        ;
        $I->haveInRepository($gameEquipment);

        $status = new Status($gameEquipment);
        $status
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC)
        ;
        $I->haveInRepository($status);

        $this->repairAction->loadParameters($actionRepair, $player, $gameEquipment);

        //Execute repair
        $this->repairAction->execute();
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(1, $player->getStatuses()->first()->getCharge());

        $this->repairAction->loadParameters($actionRepair, $player, $gameEquipment);
        //Execute repair a second time
        $this->repairAction->execute();
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());

        $this->disassembleAction->loadParameters($actionDisassemble, $player, $gameEquipment);

        $I->assertEquals(75, $this->disassembleAction->getSuccessRate());

        // check that the attempt status is still correctly set to repair
        $I->assertCount(1, $player->getStatuses());
        $I->assertEquals(StatusEnum::ATTEMPT, $player->getStatuses()->first()->getName());
        $I->assertEquals(ActionEnum::REPAIR, $player->getStatuses()->first()->getAction());
        $I->assertEquals(2, $player->getStatuses()->first()->getCharge());
    }
}
