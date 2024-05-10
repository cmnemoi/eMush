<?php

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Enum\ActionEnum;
use Mush\Modifier\Entity\Config\VariableEventModifierConfig;
use Mush\Modifier\Entity\GameModifier;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class HitActionCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private ActionConfig $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::HIT]);
        $this->action->setDirtyRate(0);

        $I->refreshEntities($this->action);

        $this->hitAction = $I->grabService(Hit::class);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2);

        $this->hitAction->execute();

        $I->assertNotEquals($this->player2->getHealthPoint(), $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function testHitFail(FunctionalTester $I)
    {
        $this->action->setSuccessRate(0);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2);

        $this->hitAction->execute();

        $I->assertEquals($this->player2->getHealthPoint(), $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function testHitArmor(FunctionalTester $I)
    {
        $this->action->setSuccessRate(100);
        $this->action->setCriticalRate(0);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2);

        /** @var VariableEventModifierConfig $armorModifierConfig */
        $armorModifierConfig = $I->grabEntityFromRepository(
            VariableEventModifierConfig::class,
            ['name' => 'armorReduceDamage']
        );
        $armorModifierConfig->setDelta(5);
        $modifier = new GameModifier($this->player2, $armorModifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($this->player2);

        $this->hitAction->execute();

        // should not lose health point because of armor
        $I->assertEquals(
            $this->player2->getHealthPoint(),
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint()
        );
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }

    public function testHitCriticalSuccess(FunctionalTester $I)
    {
        $this->action->setSuccessRate(101);
        $this->action->setCriticalRate(101);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters(
            actionConfig: $this->action,
            actionProvider: $this->player1,
            player: $this->player1,
            target: $this->player2);

        /** @var VariableEventModifierConfig $armorModifierConfig */
        $armorModifierConfig = $I->grabEntityFromRepository(VariableEventModifierConfig::class, [
            'name' => 'armorReduceDamage',
        ]);
        $armorModifierConfig->setDelta(-3);
        $modifier = new GameModifier($this->player2, $armorModifierConfig);

        $I->haveInRepository($modifier);
        $I->refreshEntities($this->player2);

        $result = $this->hitAction->execute();

        $I->assertTrue($result instanceof CriticalSuccess);

        // critical hits should bypass armor
        $I->assertNotEquals(
            $this->player2->getHealthPoint(),
            $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint()
        );
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }
}
