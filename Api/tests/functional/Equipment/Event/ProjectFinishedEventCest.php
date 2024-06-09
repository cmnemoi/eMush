<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Project\Enum\ProjectName;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ProjectFinishedEventCest extends AbstractFunctionalTest
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
    }

    #[DataProvider(method: 'shouldCreateEquipmentWhenProjectIsFinishedDataProvider')]
    public function shouldCreateEquipmentWhenProjectIsFinished(FunctionalTester $I, Example $example): void
    {
        // given I have the equipment creation places in this Daedalus
        $places = [];
        foreach ($example['creationPlaces'] as $creationPlace) {
            $places[] = $this->createExtraPlace(placeName: $creationPlace, I: $I, daedalus: $this->daedalus);
        }

        // when I finish the project
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::from($example['project'])),
            author: $this->chun,
            I: $I
        );

        // then the places should contain the equipment in the expected quantity
        foreach ($places as $place) {
            $I->assertCount(
                expectedCount: $example['quantity'],
                haystack: $place->getAllEquipmentsByName($example['equipment'])
            );
        }
    }

    #[DataProvider(method: 'shouldReplaceEquipmentWhenProjectIsFinishedDataProvider')]
    public function shouldReplaceEquipmentWhenProjectIsFinished(FunctionalTester $I, Example $example): void
    {
        $room = $this->player->getPlace();

        // given I have the equipment to replace in the room
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $example['equipmentToRemove'],
            equipmentHolder: $room,
            reasons: [],
            time: new \DateTime()
        );

        // when project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::from($example['project'])),
            author: $this->chun,
            I: $I
        );

        // then the room should not contain the equipment to remove
        $I->assertTrue(
            condition: $room->getAllEquipmentsByName($example['equipmentToRemove'])->isEmpty()
        );

        // but the room should contain the equipment to add
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getAllEquipmentsByName($example['equipmentToAdd'])
        );
    }

    private function shouldCreateEquipmentWhenProjectIsFinishedDataProvider(): array
    {
        return [
            [
                'project' => ProjectName::DISMANTLING->value,
                'equipment' => ItemEnum::METAL_SCRAPS,
                'quantity' => 5,
                'creationPlaces' => [RoomEnum::ENGINE_ROOM],
            ],
            [
                'project' => ProjectName::AUXILIARY_TERMINAL->value,
                'equipment' => EquipmentEnum::AUXILIARY_TERMINAL,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::MEDLAB, RoomEnum::ENGINE_ROOM],
            ],
            [
                'project' => ProjectName::CALL_OF_DIRTY->value,
                'equipment' => EquipmentEnum::DYNARCADE,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::ALPHA_BAY_2],
            ],
            [
                'project' => ProjectName::EXTRA_DRONE->value,
                'equipment' => ItemEnum::SUPPORT_DRONE,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::NEXUS],
            ],
            [
                'project' => ProjectName::TRASH_LOAD->value,
                'equipment' => ItemEnum::METAL_SCRAPS,
                'quantity' => 4,
                'creationPlaces' => [RoomEnum::ENGINE_ROOM],
            ],
            [
                'project' => ProjectName::TRASH_LOAD->value,
                'equipment' => ItemEnum::PLASTIC_SCRAPS,
                'quantity' => 4,
                'creationPlaces' => [RoomEnum::ENGINE_ROOM],
            ],
        ];
    }

    private function shouldReplaceEquipmentWhenProjectIsFinishedDataProvider(): array
    {
        return [
            [
                'project' => ProjectName::THALASSO->value,
                'equipmentToRemove' => EquipmentEnum::SHOWER,
                'equipmentToAdd' => EquipmentEnum::THALASSO,
            ],
            [
                'project' => ProjectName::RADAR_TRANS_VOID->value,
                'equipmentToRemove' => EquipmentEnum::ANTENNA,
                'equipmentToAdd' => EquipmentEnum::RADAR_TRANS_VOID_ANTENNA,
            ],
            [
                'project' => ProjectName::HYDROPONIC_INCUBATOR->value,
                'equipment' => EquipmentEnum::HYDROPONIC_INCUBATOR,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::HYDROPONIC_GARDEN],
            ],
            [
                'project' => ProjectName::APERO_KITCHEN->value,
                'equipmentToRemove' => EquipmentEnum::KITCHEN,
                'equipmentToAdd' => EquipmentEnum::SNC_KITCHEN,
            ],
        ];
    }
}
