<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Modifier\Service;

use Mush\Action\Actions\AccessTerminal;
use Mush\Action\Actions\Participate;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class SkillPointModifierPriorityCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    private GameEquipment $neronCore;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    public function shouldITDesignerApplyCorePointsOverITPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsITExpert($I);
        $this->givenPlayerIsDesigner($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::CONCEPTOR_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(4, SkillPointsEnum::IT_EXPERT_POINTS, $I);
    }

    public function shouldDesignerITApplyCorePointsOverITPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsDesigner($I);
        $this->givenPlayerIsITExpert($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::CONCEPTOR_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(4, SkillPointsEnum::IT_EXPERT_POINTS, $I);
    }

    public function shouldPolymathITApplyITPointsOverPolymathPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsPolymath($I);
        $this->givenPlayerIsITExpert($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(2, SkillPointsEnum::POLYMATH_IT_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::IT_EXPERT_POINTS, $I);
    }

    public function shouldITPolymathApplyITPointsOverPolymathPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsITExpert($I);
        $this->givenPlayerIsPolymath($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(2, SkillPointsEnum::POLYMATH_IT_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::IT_EXPERT_POINTS, $I);
    }

    public function shouldPolymathDesignerApplyCorePointsOverPolymathPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsPolymath($I);
        $this->givenPlayerIsDesigner($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(2, SkillPointsEnum::POLYMATH_IT_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::CONCEPTOR_POINTS, $I);
    }

    public function shouldDesignerPolymathApplyCorePointsOverPolymathPoints(FunctionalTester $I): void
    {
        $this->givenPlayerIsDesigner($I);
        $this->givenPlayerIsPolymath($I);
        $this->givenRoomHasNeronCoreAccessedByPlayer($I);
        $this->whenPlayerAdvancesProject($I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(2, SkillPointsEnum::POLYMATH_IT_POINTS, $I);
        $this->thenPlayerShouldHaveSkillPointsOfAmount(3, SkillPointsEnum::CONCEPTOR_POINTS, $I);
    }

    private function givenRoomHasNeronCoreAccessedByPlayer(FunctionalTester $I): void
    {
        $this->neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $accessTerminal = $I->grabService(AccessTerminal::class);
        $accessTerminalConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ACCESS_TERMINAL]);
        $accessTerminal->loadParameters(
            actionConfig: $accessTerminalConfig,
            actionProvider: $this->neronCore,
            player: $this->player,
            target: $this->neronCore
        );
        $accessTerminal->execute();
    }

    private function givenPlayerIsITExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I, $this->player);
    }

    private function givenPlayerIsDesigner(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::CONCEPTOR, $I, $this->player);
    }

    private function givenPlayerIsPolymath(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I, $this->player);
    }

    private function whenPlayerAdvancesProject(FunctionalTester $I): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $project->propose();

        $participateAction = $I->grabService(Participate::class);
        $participateActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PARTICIPATE]);
        $participateAction->loadParameters(
            actionConfig: $participateActionConfig,
            actionProvider: $this->neronCore,
            player: $this->player,
            target: $project
        );
        $participateAction->execute();
    }

    private function thenPlayerShouldHaveSkillPointsOfAmount(int $expectedQuantity, SkillPointsEnum $skillPoint, FunctionalTester $I)
    {
        $skillPointStatus = $this->player->getChargeStatusByNameOrThrow($skillPoint->toString());
        $skillPointQuantity = $skillPointStatus->getCharge();
        $I->assertEquals(
            expected: $expectedQuantity,
            actual: $skillPointQuantity,
            message: "Expected {$expectedQuantity} {$skillPointStatus->getName()}, got {$skillPointQuantity}"
        );
    }
}
