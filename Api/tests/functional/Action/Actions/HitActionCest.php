<?php

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;

class HitActionCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::HIT]);
        $this->action->setDirtyRate(0);

        $I->refreshEntities($this->action);

        $this->hitAction = $I->grabService(Hit::class);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $this->action->setSuccessRate(101);
        $I->refreshEntities($this->action);

        $this->hitAction->loadParameters($this->action, $this->player1, $this->player2);

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

        $this->hitAction->loadParameters($this->action, $this->player1, $this->player2);

        $this->hitAction->execute();

        $I->assertEquals($this->player2->getHealthPoint(), $this->player2->getPlayerInfo()->getCharacterConfig()->getInitHealthPoint());
        $I->assertEquals(
            $this->player1->getActionPoint(),
            $this->player1->getPlayerInfo()->getCharacterConfig()->getInitActionPoint() - $this->action->getActionCost()
        );
    }
}
