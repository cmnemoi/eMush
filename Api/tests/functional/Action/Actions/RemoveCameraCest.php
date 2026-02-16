<?php

declare(strict_types=1);

namespace Mush\tests\functional\Action\Actions;

use Mush\Action\Actions\RemoveCamera;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Entity\Place;
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
    private GameEquipment $camera;
    private Place $place;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->actionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::REMOVE_CAMERA->value]);
        $this->removeCamera = $I->grabService(RemoveCamera::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->place = $this->player->getPlace();
        $this->givenPlaceHasCamera();
    }

    public function shouldHaveTheCameraInInventory(FunctionalTester $I): void
    {
        $this->whenPlayerRemoveACamera();

        $this->thenPlayerShouldHaveACameraInInventory($I);
    }

    public function shouldCostZeroActionPointsForATechnician(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->whenPlayerWantsToRemoveCamera();

        $this->thenActionShouldCostZeroActionPoints($I);
    }

    public function shouldCostOneTechnicianPointsForATechnician(FunctionalTester $I): void
    {
        $this->givenPlayerIsATechnician($I);

        $this->whenPlayerRemoveACamera();

        $this->thenPlayerShouldHaveTechnicianPoints(1, $I);
    }

    public function shouldCostZeroActionPointsForParanoid(FunctionalTester $I): void
    {
        $this->givenPlayerIsParanoid($I);

        $this->whenPlayerWantsToRemoveCamera();

        $this->thenActionShouldCostZeroActionPoints($I);
    }

    private function givenPlaceHasCamera(): void
    {
        $this->camera = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::CAMERA_EQUIPMENT,
            equipmentHolder: $this->place,
            reasons: [],
            time: new \DateTime(),
        );
    }

    private function givenPlayerIsATechnician(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::TECHNICIAN, $I);
    }

    private function givenPlayerIsParanoid(FunctionalTester $I): void
    {
        $this->addSkillToPlayer(SkillEnum::PARANOID, $I);
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

    private function whenPlayerRemoveACamera(): void
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

    private function thenPlayerShouldHaveACameraInInventory(FunctionalTester $I): void
    {
        $I->assertTrue($this->player->hasEquipmentByName(ItemEnum::CAMERA_ITEM));
    }
}
