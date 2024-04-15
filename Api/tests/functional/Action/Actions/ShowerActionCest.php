<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Shower;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\TriggerEventModifierConfig;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

class ShowerActionCest extends AbstractFunctionalTest
{
    private Shower $showerAction;
    private Action $action;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->showerAction = $I->grabService(Shower::class);
        $this->action = $I->grabEntityFromRepository(Action::class, ['actionName' => ActionEnum::SHOWER]);
        $this->action->setInjuryRate(0);
        $I->refreshEntities($this->action);
    }

    public function testShower(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER)
        ;
        $I->haveInRepository($gameEquipment);

        $dirtyStatusConfig = $I->grabEntityFromRepository(StatusConfig::class, ['statusName' => PlayerStatusEnum::DIRTY]);
        $dirtyStatus = new Status($this->player1, $dirtyStatusConfig);
        $I->haveInRepository($dirtyStatus);

        $I->refreshEntities($this->player1);

        $this->showerAction->loadParameters($this->action, $this->player1, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint(),
            $this->player1->getHealthPoint()
        );
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost(),
            $this->player1->getActionPoint()
        );
        $I->assertCount(0, $this->player1->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testMushShower(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var ChargeStatusConfig $mushStatusConfig */
        $mushStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['statusName' => PlayerStatusEnum::MUSH]);
        $mushStatus = new ChargeStatus($this->player1, $mushStatusConfig);
        $I->haveInRepository($mushStatus);

        /** @var VariableEventModifierConfig $mushShowerModifierConfig */
        $mushShowerModifierConfig = current($I->grabEntitiesFromRepository(
            TriggerEventModifierConfig::class,
            [
            'name' => ModifierNameEnum::MUSH_SHOWER_MALUS, ]
        ));
        $mushShowerModifier = new GameModifier($this->player1, $mushShowerModifierConfig);
        $I->haveInRepository($mushShowerModifier);

        $I->refreshEntities($this->player1);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER)
        ;
        $I->haveInRepository($gameEquipment);

        $this->showerAction->loadParameters($this->action, $this->player1, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals($this->player1->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint() - 3, $this->player1->getHealthPoint());
        $I->assertEquals(
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost(),
            $this->player1->getActionPoint()
        );

        $logs = $I->grabEntitiesFromRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_MUSH,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
        $I->assertCount(1, $logs);
    }

    public function testShowerWithSoap(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER)
        ;
        $I->haveInRepository($gameEquipment);

        /** @var VariableEventModifierConfig $soapModifierConfig */
        $soapModifierConfig = current(
            $I->grabEntitiesFromRepository(
                VariableEventModifierConfig::class,
                ['name' => 'soapShowerActionModifier']
            )
        );
        $soapModifier = new GameModifier($this->player2, $soapModifierConfig);
        $I->haveInRepository($soapModifier);

        $this->showerAction->loadParameters($this->action, $this->player2, $gameEquipment);

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());

        $this->showerAction->execute();

        $I->assertEquals(
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost() + 1,
            $this->player2->getActionPoint()
        );
        $I->assertCount(0, $this->player2->getStatuses());

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player2->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
