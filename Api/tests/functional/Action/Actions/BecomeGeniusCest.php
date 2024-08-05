<?php

declare(strict_types=1);

namespace Mush\Functional\Action\Actions;

use Mush\Action\Actions\BecomeGenius;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class BecomeGeniusCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private BecomeGenius $becomeGenius;
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::BECOME_GENIUS]);
        $this->becomeGenius = $I->grabService(BecomeGenius::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->givenPlayerIsGenius($I);
    }

    public function shouldCreateGeniusIdeaStatus(FunctionalTester $I): void
    {
        $this->whenPlayerExecutesBecomeGeniusAction();

        $this->thenPlayerShouldHaveGeniusIdeaStatus($I);
    }

    public function shouldBeAvailableOncePerPlayer(FunctionalTester $I): void
    {
        $this->givenPlayerExecutesBecomeGeniusAction();

        $this->whenPlayerExecutesBecomeGeniusAction();

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I
        );
    }

    private function givenPlayerIsGenius(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->addSkillConfig(
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::GENIUS])
        );
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::GENIUS, $this->player));
    }

    private function givenPlayerExecutesBecomeGeniusAction(): void
    {
        $this->whenPlayerExecutesBecomeGeniusAction();
    }

    private function whenPlayerExecutesBecomeGeniusAction(): void
    {
        $this->becomeGenius->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->player,
            player: $this->player,
            target: null,
        );
        $this->becomeGenius->execute();
    }

    private function thenPlayerShouldHaveGeniusIdeaStatus(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasStatus(PlayerStatusEnum::GENIUS_IDEA));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: $message,
            actual: $this->becomeGenius->cannotExecuteReason(),
        );
    }
}
