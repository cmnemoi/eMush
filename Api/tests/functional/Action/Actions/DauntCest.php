<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Daunt;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DauntCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Daunt $dauntAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::DAUNT->value]);
        $this->dauntAction = $I->grabService(Daunt::class);

        $this->addSkillToPlayer(SkillEnum::INTIMIDATING, $I);
    }

    public function shouldRemoveActionPointsToTarget(FunctionalTester $I): void
    {
        $this->givenKuanTiHasActionPoints(2);

        $this->whenChunDauntsKuanTi();

        $this->thenKuanTiShouldHaveActionPoints(0, $I);
    }

    private function givenKuanTiHasActionPoints(int $actionPoints): void
    {
        $this->kuanTi->setActionPoint($actionPoints);
    }

    private function whenChunDauntsKuanTi(): void
    {
        $this->dauntAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi
        );
        $this->dauntAction->execute();
    }

    private function thenKuanTiShouldHaveActionPoints(int $actionPoints, FunctionalTester $I): void
    {
        $I->assertEquals($actionPoints, $this->kuanTi->getActionPoint());
    }
}
