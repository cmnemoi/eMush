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

    private function whenIAddSkillToPlayer(SkillEnum $skill): void
    {
        $this->addSkillToPlayerService->execute($skill, $this->player);
    }

    private function thenPlayerShouldHaveSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $player = $I->grabEntityFromRepository(Player::class, ['id' => $this->player->getId()]);

        $I->assertTrue($player->hasSkill($skill));
    }
}
