<?php

namespace functional\Action\Actions;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;

class HitActionCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        /* @var Action $action */
        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::HIT]);
        $this->action
            ->setDirtyRate(0)
        ;
        $I->refreshEntities($this->action);

        $this->characterConfig->setActions(new ArrayCollection([$this->action]));

        $I->refreshEntities($this->player, $this->characterConfig);

        $this->hitAction = $I->grabService(Hit::class);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $this->action->setSuccessRate(101);

        $this->hitAction->loadParameters($this->action, $this->player, $this->otherPlayer);

        $this->hitAction->execute();

        $I->assertNotEquals($this->otherPlayer->getHealthPoint(), $this->getBasePlayerHealthPoint());
        $I->assertEquals($this->player->getActionPoint(), $this->getBasePlayerActionPoint() - $this->action->getActionCost());
    }

    public function testHitFail(FunctionalTester $I)
    {
        $this->action->setSuccessRate(0);

        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->buildName(StatusEnum::ATTEMPT)
        ;
        $I->haveInRepository($statusConfig);

        $this->gameConfig->setStatusConfigs(new ArrayCollection([$statusConfig]));

        $this->hitAction->loadParameters($this->action, $this->player, $this->otherPlayer);

        $this->hitAction->execute();

        $I->assertEquals($this->otherPlayer->getHealthPoint(), $this->getBasePlayerHealthPoint());
        $I->assertEquals($this->player->getActionPoint(), $this->getBasePlayerActionPoint() - $this->action->getActionCost());
    }
}
