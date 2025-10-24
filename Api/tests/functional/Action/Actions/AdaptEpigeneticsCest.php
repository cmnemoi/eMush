<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\AdaptEpigenetics;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class AdaptEpigeneticsCest extends AbstractFunctionalTest
{
    private ActionConfig $adaptEpigeneticsActionConfig;
    private AdaptEpigenetics $adaptEpigenetics;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->adaptEpigeneticsActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::ADAPT_EPIGENETICS]);
        $this->adaptEpigenetics = $I->grabService(AdaptEpigenetics::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenKuanTiIsMush();
        $this->givenKuanTiHasSkill(SkillEnum::EPIGENETICS, $I);
    }

    public function shouldClearOtherMushSkills(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSkill(SkillEnum::FERTILE, $I);
        $this->whenKuanTiAdapts();
        $this->thenKuanTiDoesNotHaveSkill(SkillEnum::FERTILE, $I);
    }

    public function shouldNotClearEpigenetics(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSkill(SkillEnum::FERTILE, $I);
        $this->whenKuanTiAdapts();
        $this->thenKuanTiHasSkill(SkillEnum::EPIGENETICS, $I);
    }

    public function shouldNotClearHumanSkills(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSkill(SkillEnum::CONCEPTOR, $I);
        $this->whenKuanTiAdapts();
        $this->thenKuanTiHasSkill(SkillEnum::CONCEPTOR, $I);
    }

    public function shouldNotBeUsableTwice(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSkill(SkillEnum::FERTILE, $I);
        $this->whenKuanTiAdapts();
        $this->whenKuanTiTriesToAdapt();
        $this->thenActionShouldNotBeExecutableWithMessage(
            message: ActionImpossibleCauseEnum::UNIQUE_ACTION,
            I: $I
        );
    }

    public function shouldCreateHasAdaptedEpigeneticsStatus(FunctionalTester $I): void
    {
        $this->givenKuanTiHasSkill(SkillEnum::FERTILE, $I);
        $this->whenKuanTiAdapts();
        $this->thenKuanTiHasStatusHasAdaptedEpigenetics($I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime()
        );
    }

    private function whenKuanTiTriesToAdapt(): void
    {
        $this->adaptEpigenetics->loadParameters(
            actionConfig: $this->adaptEpigeneticsActionConfig,
            actionProvider: $this->kuanTi,
            player: $this->kuanTi,
            parameters: []
        );
    }

    private function whenKuanTiAdapts(): void
    {
        $this->whenKuanTiTriesToAdapt();
        $this->adaptEpigenetics->execute();
    }

    private function givenKuanTiHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $this->addSkillToPlayer($skill, $I, $this->kuanTi);
    }

    private function thenKuanTiDoesNotHaveSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertFalse($this->kuanTi->hasSkill($skill));
    }

    private function thenKuanTiHasSkill(SkillEnum $skill, FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasSkill($skill));
    }

    private function thenKuanTiHasStatusHasAdaptedEpigenetics(FunctionalTester $I): void
    {
        $I->assertTrue($this->kuanTi->hasStatus(PlayerStatusEnum::HAS_ADAPTED_EPIGENETICS));
    }

    private function thenActionShouldNotBeExecutableWithMessage(string $message, FunctionalTester $I): void
    {
        $I->assertEquals($message, $this->adaptEpigenetics->cannotExecuteReason());
    }
}
