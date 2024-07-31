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

    public function shouldChooseSkill(FunctionalTester $I): void
    {
        $this->whenIAddSkillToChun(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenChunHasSkill(SkillEnum::MANKIND_ONLY_HOPE, $I);
    }

    public function shouldCreateSkillModifierForDaedalus(FunctionalTester $I): void
    {
        $this->whenIAddSkillToChun(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenDaedalusShouldHaveOnlyHopeModifier($I);
    }

    public function shouldCreateSkillModifierForPlayer(FunctionalTester $I): void
    {
        $this->whenIAddSkillToKuanTi(SkillEnum::TECHNICIAN);

        $this->thenKuanTiShouldHaveTechnicianModifier($I);
    }

    private function whenIAddSkillToChun(SkillEnum $skill): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->chun));
    }

    private function thenChunHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasSkill($skill));
    }

    private function thenDaedalusShouldHaveOnlyHopeModifier(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->daedalus->getModifiers()->filter(
                static fn ($modifier) => $modifier->getModifierConfig()->getName() === 'modifier_for_daedalus_+1moral_on_day_change'
            )
        );
    }

    private function whenIAddSkillToKuanTi(SkillEnum $skill): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->kuanTi));
    }

    private function thenKuanTiShouldHaveTechnicianModifier(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->kuanTi->getModifiers()->filter(
                static fn ($modifier) => $modifier->getModifierConfig()->getName() === 'modifier_technician_double_repair_and_renovate_chance'
            )
        );
    }
}
