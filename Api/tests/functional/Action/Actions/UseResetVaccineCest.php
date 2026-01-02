<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\UseResetVaccine;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class UseResetVaccineCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private UseResetVaccine $useResetVaccine;

    private GameItem $resetVaccine;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::USE_RESET_VACCINE]);
        $this->useResetVaccine = $I->grabService(UseResetVaccine::class);

        $this->resetVaccine = $this->createEquipment(ItemEnum::RESET_VACCINE, $this->chun);
        $this->givenChunHasHerSkills();
    }

    public function randomizesAvailablePerks(FunctionalTester $I): void
    {
        $this->whenChunUsesTheVaccine();

        $this->thenChunsSetOfSkillsIsDifferent($I);
    }

    public function resetsSelectedPerks(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MANKIND_ONLY_HOPE, $I, $this->chun);
        $this->addSkillToPlayer(SkillEnum::NURSE, $I, $this->chun);
        $this->addSkillToPlayer(SkillEnum::LETHARGY, $I, $this->chun);

        $this->whenChunUsesTheVaccine();

        $this->thenChunHasNoSkillsSelected($I);
    }

    private function whenChunUsesTheVaccine(): void
    {
        $this->useResetVaccine->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->resetVaccine,
            player: $this->chun,
            target: $this->resetVaccine
        );
        $this->useResetVaccine->execute();
    }

    private function givenChunHasHerSkills(): void
    {
        $this->chun->setAvailableHumanSkills($this->chun->getCharacterConfig()->getSkillConfigs());
    }

    private function thenChunsSetOfSkillsIsDifferent(FunctionalTester $I): void
    {
        // fun math exercise: what are the odds of this test flaking? It's gotta be up in the double digit e notations.
        $I->assertNotEquals($this->chun->getCharacterConfig()->getSkillConfigs(), $this->chun->getAvailableHumanSkills());
    }

    private function thenChunHasNoSkillsSelected(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->chun->getSkills()->count());
    }
}
