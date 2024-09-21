<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Skill\Service\DeletePlayerSkillService;

use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeletePlayerSkillServiceCest extends AbstractFunctionalTest
{
    private DeletePlayerSkillService $deletePlayerSkill;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->deletePlayerSkill = $I->grabService(DeletePlayerSkillService::class);
    }

    public function shouldDeletePlaceRangedSkillModifiers(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I);

        $this->deletePlayerSkill->execute(SkillEnum::SHRINK, $this->player);

        $I->assertFalse($this->player->getPlace()->hasModifierByModifierName(ModifierNameEnum::SHRINK_MODIFIER));
    }
}
