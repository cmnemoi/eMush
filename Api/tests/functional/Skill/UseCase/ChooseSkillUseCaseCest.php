<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Modifier\Event;

use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChooseSkillUseCaseCest extends AbstractFunctionalTest
{
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);
    }

    public function shouldAddSkillToPlayer(FunctionalTester $I): void
    {
        $this->whenChunChoosesSkill(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenChunHasSkill(SkillEnum::MANKIND_ONLY_HOPE, $I);
    }

    private function whenChunChoosesSkill(SkillEnum $skill): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->chun));
    }

    private function thenChunHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasSkill($skill));
    }
}
