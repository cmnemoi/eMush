<?php

namespace Mush\Tests\functional\Action\Event;

use Mush\Action\Actions\Shower;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ActionCostModificationCapCest extends AbstractFunctionalTest
{
    private Shower $showerAction;
    private ActionConfig $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->showerAction = $I->grabService(Shower::class);
        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::TAKE_SHOWER]);
        $this->action->setInjuryRate(0);
        $I->refreshEntities($this->action);
    }

    public function testCostGoNegative(FunctionalTester $I): void
    {
        $room = $this->daedalus->getPlaceByName(RoomEnum::LABORATORY);

        /** @var EquipmentConfig $equipmentConfig */
        $equipmentConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::SHOWER]);

        $gameEquipment = new GameEquipment($room);
        $gameEquipment
            ->setEquipment($equipmentConfig)
            ->setName(EquipmentEnum::SHOWER);
        $I->haveInRepository($gameEquipment);

        /** @var VariableEventModifierConfig $soapModifierConfig */
        $soapModifierConfig = current(
            $I->grabEntitiesFromRepository(
                VariableEventModifierConfig::class,
                ['name' => 'soapShowerActionModifier']
            )
        );
        $soapModifierConfigImproved = clone $soapModifierConfig;
        $soapModifierConfigImproved->setDelta(-6)->setName('soapModifierImprovedTest');
        $I->haveInRepository($soapModifierConfigImproved);

        $soapModifier = new GameModifier($this->player2, $soapModifierConfigImproved);
        $soapModifier->setModifierProvider($gameEquipment);
        $I->haveInRepository($soapModifier);

        $this->showerAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $gameEquipment,
            player: $this->player2,
            target: $gameEquipment
        );

        $initActionPoints = $this->player2->getPlayerInfo()->getCharacterConfig()->getInitActionPoint();
        $this->action->getActionCost();

        $I->assertTrue($this->showerAction->isVisible());
        $I->assertNull($this->showerAction->cannotExecuteReason());
        $I->assertEquals($this->showerAction->getActionPointCost(), 0);

        $this->showerAction->execute();

        $I->assertEquals(
            $initActionPoints,
            $this->player2->getActionPoint()
        );

        $I->seeInRepository(RoomLog::class, [
            'place' => $room->getName(),
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player2->getPlayerInfo()->getId(),
            'log' => ActionLogEnum::SHOWER_HUMAN,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }
}
