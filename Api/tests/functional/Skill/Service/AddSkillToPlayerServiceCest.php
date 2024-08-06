<?php

declare(strict_types=1);

namespace Mush\tests\functional\Skill\Service;

use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\AddSkillToPlayerService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AddSkillToPlayerServiceCest extends AbstractFunctionalTest
{
    private AddSkillToPlayerService $addSkillToPlayerService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->addSkillToPlayerService = $I->grabService(AddSkillToPlayerService::class);
    }

    public function shouldAddSkillToPlayer(FunctionalTester $I): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::TECHNICIAN);

        $this->thenPlayerShouldHaveSkill(SkillEnum::TECHNICIAN, $I);
    }

    public function shouldCreateSkillModifierForDaedalus(FunctionalTester $I): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenDaedalusShouldHaveOnlyHopeModifier($I);
    }

    public function shouldCreateSkillModifierForPlayer(FunctionalTester $I): void
    {
        $this->whenIAddSkillToPlayer(SkillEnum::TECHNICIAN);

        $this->thenPlayerShouldHaveTechnicianModifier($I);
    }

    private function whenIAddSkillToPlayer(SkillEnum $skill): void
    {
        $this->addSkillToPlayerService->execute($skill, $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $player = $I->grabEntityFromRepository(Player::class, ['id' => $this->player->getId()]);

        $I->assertTrue($player->hasSkill($skill));
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

    private function thenPlayerShouldHaveTechnicianModifier(FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->player->getModifiers()->filter(
                static fn ($modifier) => $modifier->getModifierConfig()->getName() === 'modifier_technician_double_repair_and_renovate_chance'
            )
        );
    }
}
