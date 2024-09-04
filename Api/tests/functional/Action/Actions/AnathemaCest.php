<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\Anathema;
use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AnathemaCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Anathema $anathema;
    private ActionConfig $attemptActionConfig;
    private Hit $attemptAction;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ANATHEMA]);
        $this->anathema = $I->grabService(Anathema::class);
        $this->attemptActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->attemptAction = $I->grabService(Hit::class);

        $this->addSkillToPlayer(SkillEnum::VICTIMIZER, $I);
    }

    public function shouldCreatePariahStatus(FunctionalTester $I): void
    {
        $this->whenChunUsesAnathemaOnKuanTi();

        $this->thenKuanTiShouldHavePariahStatus($I);
    }

    public function pariahShouldHaveAMalusWhenAttemptingActions(FunctionalTester $I): void
    {
        $this->givenChunUsesAnathemaOnKuanTi();

        $this->givenActionSuccessRateIs(60);

        $this->whenKuanTiAttemptsAction();

        $this->thenActionSuccessRateShouldBe(48, $I);
    }

    private function givenChunUsesAnathemaOnKuanTi(): void
    {
        $this->whenChunUsesAnathemaOnKuanTi();
    }

    private function givenActionSuccessRateIs(int $actionSuccessRate): void
    {
        $this->attemptActionConfig->setSuccessRate($actionSuccessRate);
    }

    private function whenKuanTiAttemptsAction(): void
    {
        $this->attemptAction->loadParameters(
            actionConfig: $this->attemptActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            target: $this->chun,
        );
    }

    private function whenChunUsesAnathemaOnKuanTi(): void
    {
        $this->anathema->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->chun,
            target: $this->kuanTi,
        );
        $this->anathema->execute();
    }

    private function thenKuanTiShouldHavePariahStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::PARIAH));
    }

    private function thenActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->attemptAction->getSuccessRate());
    }
}
