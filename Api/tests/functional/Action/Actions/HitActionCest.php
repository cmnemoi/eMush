<?php

namespace functional\Action\Actions;

use App\Tests\AbstactFunctionalTest;
use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\StatusEnum;

class HitActionCest extends AbstactFunctionalTest
{
    private Hit $hitAction;
    private Action $action;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->hitAction = $I->grabService(Hit::class);

        /* @var Action $action */
        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::HIT]);
        $this->action
            ->setSuccessRate(101)
            ->setDirtyRate(0)
        ;
        $this->characterConfig->setActions(new ArrayCollection([$this->action]));

        $this->player->setActionPoint(1);
        $this->otherPlayer->setHealthPoint(10);

        $I->refreshEntities($this->player, $this->characterConfig);
    }

    public function testHitSuccess(FunctionalTester $I)
    {
        $this->hitAction->loadParameters($this->action, $this->player, $this->otherPlayer);

        $this->hitAction->execute();

        $I->assertNotEquals($this->otherPlayer->getHealthPoint(), 10);
        $I->assertEquals($this->player->getActionPoint(), 0);
    }

    public function testHitFail(FunctionalTester $I)
    {
        $statusConfig = new ChargeStatusConfig();
        $statusConfig
            ->setStatusName(StatusEnum::ATTEMPT)
            ->buildName(StatusEnum::ATTEMPT)
        ;
        $I->haveInRepository($statusConfig);

        $this->gameConfig->setStatusConfigs(new ArrayCollection([$statusConfig]));

        $this->action->setSuccessRate(0);

        $this->hitAction->loadParameters($this->action, $this->player, $this->otherPlayer);

        $this->hitAction->execute();

        $I->assertEquals($this->otherPlayer->getHealthPoint(), 10);
        $I->assertEquals($this->player->getActionPoint(), 0);
    }
}
