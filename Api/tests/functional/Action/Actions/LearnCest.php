<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Learn;
use Mush\Action\Entity\ActionConfig;
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
final class LearnCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Learn $learn;
    private ChooseSkillUseCase $chooseSkillUseCase;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::LEARN]);
        $this->learn = $I->grabService(Learn::class);
        $this->chooseSkillUseCase = $I->grabService(ChooseSkillUseCase::class);

        $this->givenChunHasApprenticeSkill($I);
    }

    public function shouldAddLearnedSkillToPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasTechnicianSkill($I);

        $this->whenChunLearnsTechnicianSkill();

        $this->thenChunShouldHaveTechnicianSkill($I);
    }

    public function shouldDeleteApprenticeshipSkillAfterLearning(FunctionalTester $I): void
    {
        $this->givenChunHasApprenticeSkill($I);

        $this->whenChunLearnsTechnicianSkill();

        $this->thenChunShouldNotHaveApprenticeSkill($I);
    }

    private function givenChunHasApprenticeSkill(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::APPRENTICE]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::APPRENTICE, $this->chun));
    }

    private function givenKuanTiHasTechnicianSkill(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::TECHNICIAN]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::TECHNICIAN, $this->kuanTi));
    }

    private function whenChunLearnsTechnicianSkill(): void
    {
        $this->learn->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->player,
            target: $this->kuanTi,
            parameters: ['skill' => SkillEnum::TECHNICIAN->toString()]
        );
        $this->learn->execute();
    }

    private function thenChunShouldHaveTechnicianSkill(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->hasSkill(SkillEnum::TECHNICIAN));
    }

    private function thenChunShouldNotHaveApprenticeSkill(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->hasSkill(SkillEnum::APPRENTICE));
    }
}
