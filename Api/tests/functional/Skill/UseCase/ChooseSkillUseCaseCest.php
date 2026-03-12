<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Modifier\Event;

use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\UseCase\ChooseSkillUseCase;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChooseSkillUseCaseCest extends AbstractFunctionalTest
{
    private ChooseSkillUseCase $chooseSkillUseCase;

    private Player $chao;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->chao = $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::CHAO);
    }

    public function shouldAddSkillToPlayer(FunctionalTester $I): void
    {
        $this->givenChunHasHerSkills();

        $this->whenChunChoosesSkill(SkillEnum::MANKIND_ONLY_HOPE);

        $this->thenChunHasSkill(SkillEnum::MANKIND_ONLY_HOPE, $I);
    }

    public function shouldCreateSkillPoints(FunctionalTester $I): void
    {
        $this->givenChaoHasHisSkills();

        $this->whenChaoChoosesSkill(SkillEnum::SHOOTER);

        $this->thenChaoHasShooterPoints($I);
    }

    private function givenChunHasHerSkills(): void
    {
        $this->chun->setAvailableHumanSkills($this->chun->getCharacterConfig()->getSkillConfigs());
    }

    private function givenChaoHasHisSkills(): void
    {
        $this->chao->setAvailableHumanSkills($this->chao->getCharacterConfig()->getSkillConfigs());
    }

    private function whenChunChoosesSkill(SkillEnum $skill): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->chun));
    }

    private function whenChaoChoosesSkill(SkillEnum $skill): void
    {
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->chao));
    }

    private function thenChunHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasSkill($skill));
    }

    private function thenChaoHasShooterPoints(FunctionalTester $I): void
    {
        $I->assertTrue($this->chao->hasStatus(SkillPointsEnum::SHOOT_POINTS->toString()));
    }
}
