<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Learn;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Game\Enum\CharacterEnum;
use Mush\Game\Exception\GameException;
use Mush\Skill\Entity\SkillConfig;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class LearnCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private Learn $learn;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::LEARN]);
        $this->learn = $I->grabService(Learn::class);
        $this->givenChunHasSkill(SkillEnum::APPRENTICE, $I);
    }

    public function shouldNotBeExecutableIfThereIsNoOneInTheRoom(FunctionalTester $I): void
    {
        $this->givenKuanTiOnPlanet();

        $this->whenChunTriesToLearnSkill(SkillEnum::TECHNICIAN);

        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::LONELY_APPRENTICESHIP,
            I: $I
        );
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

    public function shouldMakeApprenticeSkillUnavailableAfterLearning(FunctionalTester $I): void
    {
        $this->givenKuanTiHasTechnicianSkill($I);

        $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);

        $this->thenApprenticeSkillIsUnavailableForChun($I);
    }

    public function shouldAddLearnedSkillToAvailableSkills(FunctionalTester $I): void
    {
        $this->givenKuanTiHasTechnicianSkill($I);

        $this->whenChunLearnsSkill(SkillEnum::TECHNICIAN);

        $this->thenTechnicianSkillIsAvailableForChun($I);
    }

    private function givenChunHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->addSkillToPlayer($skill, $I, $this->player);
    }

    private function givenKuanTiHasTechnicianSkill(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I, $this->player2);
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
        $this->addSkillToPlayer(SkillEnum::ANONYMUSH, $I, $this->player2);
    }

    private function whenChunTriesToLearnSkill(SkillEnum $skill): void
    {
        $this->learn->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->chun,
            player: $this->player,
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
        $I->assertFalse($this->chun->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::APPRENTICE])));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->learn->cannotExecuteReason());
    }

    private function thenApprenticeSkillIsUnavailableForChun(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->cannotTakeSkill(SkillEnum::APPRENTICE));
    }

    private function thenTechnicianSkillIsAvailableForChun(FunctionalTester $I): void
    {
        $I->assertTrue($this->chun->getAvailableHumanSkills()->contains($I->grabEntityFromRepository(SkillConfig::class, ['name' => SkillEnum::TECHNICIAN])));
    }
}
