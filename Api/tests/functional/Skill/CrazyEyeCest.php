<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class CrazyEyeCest extends AbstractFunctionalTest
{
    private Hit $hitAction;
    private ActionConfig $hitActionConfig;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->hitAction = $I->grabService(Hit::class);
        $this->hitActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->addSkillToPlayer(SkillEnum::CRAZY_EYE, $I, $this->kuanTi);
    }

    public function shouldCostThreeApHittingCrazyEyePlayer(FunctionalTester $I): void
    {
        $this->givenChunHasActionPoints(3);
        $this->whenChunHitsCrazyEyePlayer();
        $I->assertEquals(0, $this->chun->getActionPoint());
    }

    private function givenChunHasActionPoints(int $actionPoints): void
    {
        $this->chun->setActionPoint($actionPoints);
    }

    private function whenChunHitsCrazyEyePlayer(): void
    {
        $this->hitAction->loadParameters(
            actionConfig: $this->hitActionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->hitAction->execute();
    }
}
