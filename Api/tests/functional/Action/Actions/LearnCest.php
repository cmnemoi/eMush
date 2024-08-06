<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Learn;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Exception\GameException;
use Mush\Skill\Dto\ChooseSkillDto;
use Mush\Skill\Entity\Skill;
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

        $this->givenChunHasSkill(SkillEnum::APPRENTICE, $I);
    }

    public function shouldNotBeExecutableIfThereIsNoOneInTheRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiOnPlanet($I);

        $this->whenChunTriesToLearnSkill(SkillEnum::TECHNICIAN);

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::LONELY_APPRENTICESHIP,
            I: $I
        );
    }

    public function shouldAddLearnedSkillToPlayer(FunctionalTester $I): void
    {
        $this->givenKuanTiHasTechnicianSkill($I);

        $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);

        $this->thenChunShouldHaveTechnicianSkill($I);
    }

    public function shouldDeleteApprenticeshipSkillAfterLearning(FunctionalTester $I): void
    {
        $this->givenKuanTiHasTechnicianSkill($I);

        $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);

        $this->thenChunShouldNotHaveApprenticeSkill($I);
    }

    public function shouldThrowIfTryingToLearnASkillAlreadyPossessed(FunctionalTester $I): void
    {
        $this->givenChunHasSkill(SkillEnum::TECHNICIAN, $I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);
        });
    }

    public function shouldThrowIfTryingToLearnSkillNotInTheRoom(FunctionalTester $I): void
    {
        $this->givenDerekIsInTheRoom($I);

        $this->givenKuanTiOnPlanet();

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);
        });
    }

    public function shouldThrowIfTryingToLearnMushSkill(FunctionalTester $I): void
    {
        $this->givenKuanTiHasAnonymousSkill($I);

        $I->expectThrowable(GameException::class, function () {
            $this->whenChunLearnsSkill(SkillEnum::ANONYMUSH);
        });
    }

    private function givenChunHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => $skill]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto($skill, $this->chun));
    }

    private function givenKuanTiHasTechnicianSkill(FunctionalTester $I): void
    {
        $this->player->getCharacterConfig()->setSkillConfigs([
            $I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::TECHNICIAN]),
        ]);
        $this->chooseSkillUseCase->execute(new ChooseSkillDto(SkillEnum::TECHNICIAN, $this->kuanTi));
    }

    private function givenKuanTiOnPlanet(): void
    {
        $this->kuanTi->changePlace($this->daedalus->getPlanetPlace());
    }

    private function givenDerekIsInTheRoom(FunctionalTester $I): void
    {
        $this->addPlayerByCharacter($I, $this->daedalus, CharacterEnum::DEREK);
    }

    private function givenKuanTiHasAnonymousSkill(FunctionalTester $I): void
    {
        $skillConfig = new SkillConfig(SkillEnum::ANONYMUSH);
        $I->haveInRepository($skillConfig);
        new Skill($skillConfig, $this->kuanTi);
    }

    private function whenChunTriesToLearnSkill(SkillEnum $skill): void
    {
        $this->learn->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->player,
            target: $this->kuanTi,
            parameters: ['skill' => $skill->toString()]
        );
    }

    private function whenChunLearnsSkill(SkillEnum $skill): void
    {
        $this->whenChunTriesToLearnSkill($skill);
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

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->learn->cannotExecuteReason());
    }
}
