<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Skill\Service\DeletePlayerSkillService;

use Mush\Action\Actions\AccessTerminal;
use Mush\Action\Actions\Cure;
use Mush\Action\Actions\Participate;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Project\Entity\Project;
use Mush\Project\Enum\ProjectName;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Service\DeletePlayerSkillService;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Enum\SkillPointsEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DeletePlayerSkillServiceCest extends AbstractFunctionalTest
{
    private DeletePlayerSkillService $deletePlayerSkill;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private GameEquipment $neronCore;
    private Project $project;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->deletePlayerSkill = $I->grabService(DeletePlayerSkillService::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function shouldDeletePlaceRangedSkillModifiers(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::SHRINK, $I);

        $this->deletePlayerSkill->execute(SkillEnum::SHRINK, $this->player);

        $I->assertFalse($this->player->getPlace()->hasModifierByModifierName(ModifierNameEnum::SHRINK_MODIFIER));
    }

    public function shouldITPolymathHaveITPointsDeletedOnCuredMush(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();
        $this->givenKuanTiIsITExpert($I);
        $this->givenKuanTiIsPolymath($I);
        $this->whenKuanTiIsCured($I);
        $this->thenKuanTiShouldHaveNoITPoints($I);
    }

    public function shouldPolymathITHaveITPointsDeletedOnCuredMush(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();
        $this->givenKuanTiIsPolymath($I);
        $this->givenKuanTiIsITExpert($I);
        $this->whenKuanTiIsCured($I);
        $this->thenKuanTiShouldHaveNoITPoints($I);
    }

    public function shouldITPolymathHaveFourITPointsRemovedOnItExpertLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsITExpert($I);
        $this->givenKuanTiIsPolymath($I);
        $this->whenKuanTiHasITExpertRemoved();
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(2, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(2, $I);
    }

    public function shouldPolymathITHaveFourITPointsRemovedOnItExpertLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsPolymath($I);
        $this->givenKuanTiIsITExpert($I);
        $this->whenKuanTiHasITExpertRemoved();
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(2, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(2, $I);
    }

    public function shouldITPolymathHaveTwoITPointsRemovedOnPolymathLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsITExpert($I);
        $this->givenKuanTiIsPolymath($I);
        $this->whenKuanTiHasPolymathRemoved();
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(4, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(4, $I);
    }

    public function shouldPolymathITHaveTwoITPointsRemovedOnPolymathLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsPolymath($I);
        $this->givenKuanTiIsITExpert($I);
        $this->whenKuanTiHasPolymathRemoved();
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(4, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(4, $I);
    }

    public function shouldITPolymathCorrectlySpendITPointAfterITExpertLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsITExpert($I);
        $this->givenKuanTiIsPolymath($I);
        $this->givenRoomHasNeronCoreAccessedByKuanTiWithProjectAvailable($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiHasITExpertRemoved();
        $this->whenKuanTiAdvancesProject($I);
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(2, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(1, $I);
    }

    public function shouldITPolymathCorrectlySpendITPointAfterPolymathLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsITExpert($I);
        $this->givenKuanTiIsPolymath($I);
        $this->givenRoomHasNeronCoreAccessedByKuanTiWithProjectAvailable($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiHasPolymathRemoved();
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(4, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(2, $I);
    }

    public function shouldPolymathITCorrectlySpendITPointAfterITExpertLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsPolymath($I);
        $this->givenKuanTiIsITExpert($I);
        $this->givenRoomHasNeronCoreAccessedByKuanTiWithProjectAvailable($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiHasITExpertRemoved();
        $this->whenKuanTiAdvancesProject($I);
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(2, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(1, $I);
    }

    public function shouldPolymathITCorrectlySpendITPointAfterPolymathLoss(FunctionalTester $I): void
    {
        $this->givenKuanTiIsPolymath($I);
        $this->givenKuanTiIsITExpert($I);
        $this->givenRoomHasNeronCoreAccessedByKuanTiWithProjectAvailable($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiHasPolymathRemoved();
        $this->whenKuanTiAdvancesProject($I);
        $this->whenKuanTiAdvancesProject($I);
        $this->thenKuanTiShouldHaveMaxITPointsOfAmount(4, $I);
        $this->thenKuanTiShouldHaveITPointsOfAmount(2, $I);
    }

    public function shouldMaintenanceCrewLoseAllPointTypesOnCuredMush(FunctionalTester $I): void
    {
        $this->givenKuanTiIsMush();
        $this->givenKuanTiIsMaintenanceAgent($I);
        $this->whenKuanTiIsCured($I);
        $this->thenKuanTiShouldHaveNoMaintenanceCrewPoints($I);
    }

    private function givenKuanTiIsMush(): void
    {
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::MUSH,
            holder: $this->kuanTi,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenRoomHasNeronCoreAccessedByKuanTiWithProjectAvailable(FunctionalTester $I): void
    {
        $this->neronCore = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::NERON_CORE,
            equipmentHolder: $this->kuanTi->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $accessTerminal = $I->grabService(AccessTerminal::class);
        $accessTerminalConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::ACCESS_TERMINAL]);
        $accessTerminal->loadParameters(
            actionConfig: $accessTerminalConfig,
            actionProvider: $this->neronCore,
            player: $this->kuanTi,
            target: $this->neronCore
        );
        $accessTerminal->execute();

        $this->project = $this->daedalus->getProjectByName(ProjectName::TRAIL_REDUCER);
        $this->project->propose();
    }

    private function givenKuanTiIsITExpert(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::IT_EXPERT, $I, $this->kuanTi);
    }

    private function givenKuanTiIsPolymath(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::POLYMATH, $I, $this->kuanTi);
    }

    private function givenKuanTiIsMaintenanceAgent(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::MAINTENANCE_CREW, $I, $this->kuanTi);
    }

    private function whenKuanTiIsCured(FunctionalTester $I): void
    {
        $rfs = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ToolItemEnum::RETRO_FUNGAL_SERUM,
            equipmentHolder: $this->chun,
            reasons: [],
            time: new \DateTime(),
        );

        $cure = $I->grabService(Cure::class);
        $cureConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::CURE]);
        $cure->loadParameters(
            actionConfig: $cureConfig,
            actionProvider: $rfs,
            player: $this->chun,
            target: $this->kuanTi
        );
        $cure->execute();
    }

    private function whenKuanTiHasITExpertRemoved(): void
    {
        $this->deletePlayerSkill->execute(SkillEnum::IT_EXPERT, $this->kuanTi);
    }

    private function whenKuanTiHasPolymathRemoved(): void
    {
        $this->deletePlayerSkill->execute(SkillEnum::POLYMATH, $this->kuanTi);
    }

    private function whenKuanTiAdvancesProject(FunctionalTester $I): void
    {
        $participateAction = $I->grabService(Participate::class);
        $participateActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['actionName' => ActionEnum::PARTICIPATE]);
        $participateAction->loadParameters(
            actionConfig: $participateActionConfig,
            actionProvider: $this->neronCore,
            player: $this->kuanTi,
            target: $this->project
        );
        $participateAction->execute();
    }

    private function thenKuanTiShouldHaveNoITPoints(FunctionalTester $I)
    {
        $computerPointsStatus = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::COMPUTER_POINTS->toString());
        $I->assertNull($computerPointsStatus, 'Computer points charge status exists.');
    }

    private function thenKuanTiShouldHaveMaxITPointsOfAmount(int $expectedQuantity, FunctionalTester $I)
    {
        $computerPointsMaxQuantity = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::COMPUTER_POINTS->toString())?->getMaxChargeOrThrow();
        $I->assertEquals(
            expected: $expectedQuantity,
            actual: $computerPointsMaxQuantity,
            message: "Expected {$expectedQuantity} max IT points, got the max {$computerPointsMaxQuantity}"
        );
    }

    private function thenKuanTiShouldHaveITPointsOfAmount(int $expectedQuantity, FunctionalTester $I)
    {
        $computerPointsQuantity = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::COMPUTER_POINTS->toString())?->getCharge();
        $I->assertEquals(
            expected: $expectedQuantity,
            actual: $computerPointsQuantity,
            message: "Expected {$expectedQuantity} IT points, got the max {$computerPointsQuantity}"
        );
    }

    private function thenKuanTiShouldHaveNoMaintenanceCrewPoints(FunctionalTester $I)
    {
        $techPointsStatus = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::ENGINEER_POINTS->toString());
        $I->assertNull($techPointsStatus, 'Tech points charge status exists.');

        $cookPointsStatus = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::COOK_POINTS->toString());
        $I->assertNull($cookPointsStatus, 'Cook points charge status exists.');

        $gardenPointsStatus = $this->kuanTi->getChargeStatusByName(SkillPointsEnum::GARDEN_POINTS->toString());
        $I->assertNull($gardenPointsStatus, 'Garden points charge status exists.');
    }
}
