<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Event;

use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private GameEquipmentService $equipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->equipmentService = $I->grabService(GameEquipmentService::class);
        $this->createExtraPlace(
            placeName: RoomEnum::HYDROPONIC_GARDEN,
            I: $I,
            daedalus: $this->daedalus
        );
    }

    public function parasiteElimProjectShouldDecreasePlantMaturationTimeInGarden(FunctionalTester $I): void
    {
        $bananaTree = $this->givenABananaTreeInPlace(RoomEnum::HYDROPONIC_GARDEN, $I);

        $this->givenBananaTreeShouldNormallyMatureIn36Cycles($I, $bananaTree);

        $this->whenParasiteElimProjectIsFinished($I);

        $this->thenBananaTreeShouldMatureIn32Cycles($I, $bananaTree);
    }

    public function parasiteElimShouldDecreasePlantMaturationTimeWhenPlantEntersGarden(FunctionalTester $I): void
    {
        $bananaTree = $this->givenABananaTreeInPlace(RoomEnum::LABORATORY, $I);

        $this->givenBananaTreeShouldNormallyMatureIn36Cycles($I, $bananaTree);

        $this->whenParasiteElimProjectIsFinished($I);

        // move banana tree into the garden
        $this->equipmentService->moveEquipmentTo(
            equipment: $bananaTree,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::HYDROPONIC_GARDEN),
        );

        $this->thenBananaTreeShouldMatureIn32Cycles($I, $bananaTree);
    }

    private function givenABananaTreeInPlace(string $placeName, FunctionalTester $I): GameItem
    {   
        if ($this->daedalus->getPlaceByName($placeName) === null) {
            $this->createExtraPlace(
                placeName: $placeName,
                I: $I,
                daedalus: $this->daedalus
            );
        }

        $place = $this->daedalus->getPlaceByNameOrThrow($placeName);

        return $this->equipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenBananaTreeShouldNormallyMatureIn36Cycles(FunctionalTester $I, GameItem $bananaTree): void
    {
        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $I->assertEquals(36, $maturationStatus->getVariableByName(EquipmentStatusEnum::PLANT_YOUNG)->getMaxValue());
    }

    private function whenParasiteElimProjectIsFinished(FunctionalTester $I): void
    {
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PARASITE_ELIM),
            author: $this->player,
            I: $I
        );
    }

    private function thenBananaTreeShouldMatureIn32Cycles(FunctionalTester $I, GameItem $bananaTree): void
    {
        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $I->assertEquals(32, $maturationStatus->getVariableByName(EquipmentStatusEnum::PLANT_YOUNG)->getMaxValue());
    }
}
