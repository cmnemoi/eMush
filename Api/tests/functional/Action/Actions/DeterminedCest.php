<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Hit;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Enum\ActionEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeterminedCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Hit $attemptAction;
    private ChooseSkillUseCase $chooseSkillUseCase;
    private ActionResult $actionResult;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::HIT]);
        $this->attemptAction = $I->grabService(Hit::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
        $this->givenPlayerIsDetermined($I);
    }

    public function shouldHaveAGreaterSuccessRateAfterAFailedAttempt(FunctionalTester $I): void
    {
        $this->givenActionHasSuccessRate(14);

        do {
            $this->whenPlayerDoesAction();
        } while ($this->actionResult->isASuccess());

        $this->thenActionSuccessRateShouldBe(18, $I);
    }

    private function givenPlayerIsDetermined(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->addSkillConfig(
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::DETERMINED])
        );
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::DETERMINED, $this->player));
    }

    private function givenActionHasSuccessRate(int $successRate): void
    {
        $this->actionConfig->setSuccessRate($successRate);
    }

    private function whenPlayerDoesAction(): void
    {
        $this->attemptAction->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: $this->player2
        );
        $this->actionResult = $this->attemptAction->execute();
    }

    private function thenActionSuccessRateShouldBe(int $expectedSuccessRate, FunctionalTester $I): void
    {
        $I->assertEquals($expectedSuccessRate, $this->attemptAction->getSuccessRate());
    }
}
