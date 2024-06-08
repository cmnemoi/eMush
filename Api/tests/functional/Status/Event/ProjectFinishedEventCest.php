<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Status\Event;

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
    }

    public function parasiteElimProjectShouldDecreasePlantMaturationTime(FunctionalTester $I): void
    {
        // given there is a garden in the Daedalus
        $garden = $this->createExtraPlace(
            placeName: RoomEnum::HYDROPONIC_GARDEN,
            I: $I,
            daedalus: $this->daedalus
        );

        // given a banana tree
        $bananaTree = $this->equipmentService->createGameEquipmentFromName(
            equipmentName: GamePlantEnum::BANANA_TREE,
            equipmentHolder: $garden,
            reasons: [],
            time: new \DateTime()
        );

        // given this banana tree should normally mature in 36 cycles
        $maturationStatus = $bananaTree->getChargeStatusByNameOrThrow(EquipmentStatusEnum::PLANT_YOUNG);
        $I->assertEquals(36, $maturationStatus->getVariableByName(EquipmentStatusEnum::PLANT_YOUNG)->getMaxValue());

        // when Parasite Elim project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::PARASITE_ELIM),
            author: $this->chun,
            I: $I
        );

        // then this banana tree should mature in 32 cycles
        $I->assertEquals(32, $maturationStatus->getVariableByName(EquipmentStatusEnum::PLANT_YOUNG)->getMaxValue());
    }
}
