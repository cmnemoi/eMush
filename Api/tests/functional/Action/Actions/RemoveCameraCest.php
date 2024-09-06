<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\RemoveCamera;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Skill\Enum\SkillEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class RemoveCameraCest extends AbstractFunctionalTest
{
    private ActionConfig $actionConfig;
    private RemoveCamera $removeCamera;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private GameItem $camera;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::INSTALL_CAMERA->value]);
        $this->removeCamera = $I->grabService(RemoveCamera::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->givenPlayerHasCamera();
    }

    public function shouldCostZeroActionPointsForATechnician(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->whenPlayerWantsToRemoveCamera();

        $this->thenActionShouldCostZeroActionPoints($I);
    }

    public function shouldCostOneChefPointsForATechnician(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->whenPlayerInstallsCamera();

        $this->thenPlayerShouldHaveTechnicianPoints(1, $I);
    }

    private function givenPlayerHasCamera(): void
    {
        $this->camera = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::CAMERA_ITEM,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsATechnician(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I);
    }

    private function whenPlayerWantsToRemoveCamera(): void
    {
        $this->removeCamera->loadParameters(
            actionConfig: $this->actionConfig,
            actionProvider: $this->camera,
            player: $this->player,
            target: $this->camera,
        );
    }

    private function whenPlayerInstallsCamera(): void
    {
        $this->whenPlayerWantsToRemoveCamera();
        $this->removeCamera->execute();
    }

    private function thenActionShouldCostZeroActionPoints(FunctionalTester $I): void
    {
        $I->assertEquals(0, $this->removeCamera->getActionPointCost());
    }

    private function thenPlayerShouldHaveTechnicianPoints(int $expectedTechnicianPoints, FunctionalTester $I): void
    {
        $I->assertEquals($expectedTechnicianPoints, $this->player->getSkillByNameOrThrow(SkillEnum::TECHNICIAN)->getSkillPoints());
    }
}
