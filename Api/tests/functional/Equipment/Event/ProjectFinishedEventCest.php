<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
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

    #[DataProvider(methodName: 'shouldCreateEquipmentWhenProjectIsFinishedDataProvider')]
    public function shouldCreateEquipmentWhenProjectIsFinished(FunctionalTester $I, Example $example): void
    {
        // given I have the equipment creation places in this Daedalus
        $places = [];
        foreach ($example['creationPlaces'] as $creationPlace) {
            // if room already exists dont create it twice
            if ($this->daedalus->getPlaceByName($creationPlace)) {
                $places[] = $this->daedalus->getPlaceByNameOrThrow($creationPlace);
            } else {
                $places[] = $this->createExtraPlace(placeName: $creationPlace, I: $I, daedalus: $this->daedalus);
            }
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

    #[DataProvider(methodName: 'shouldReplaceEquipmentWhenProjectIsFinishedDataProvider')]
    public function shouldReplaceEquipmentWhenProjectIsFinished(FunctionalTester $I, Example $example): void
    {
        $room = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

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

    #[DataProvider(methodName: 'shouldReplaceEquipmentOnlyInSpecifiedPlaceAndQuantityDataProvider')]
    public function shouldReplaceEquipmentOnlyInSpecifiedPlaceAndQuantity(FunctionalTester $I, Example $example): void
    {
        $room = $this->daedalus->getPlaceByNameOrThrow($example['place']);

        // given I have the equipment to replace in the lab twice
        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $example['equipmentToRemove'],
                equipmentHolder: $room,
                reasons: [],
                time: new \DateTime()
            );
        }

        // given I have it in space as well
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $example['equipmentToRemove'],
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: [],
            time: new \DateTime()
        );

        // when project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::from($example['project'])),
            author: $this->chun,
            I: $I
        );

        // then the lab should one piece of equipment to remove
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getAllEquipmentsByName($example['equipmentToRemove']),
            message: 'Lab should contain one piece of equipment to remove'
        );

        // but the lab should contain the equipment to add
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getAllEquipmentsByName($example['equipmentToAdd']),
            message: 'Lab should contain the equipment to add'
        );

        // space should still contain the equipment to remove
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->daedalus->getSpace()->getAllEquipmentsByName($example['equipmentToRemove']),
            message: 'Space should still contain the equipment to remove'
        );

        // and not the equipment to add
        $I->assertTrue(
            condition: $this->daedalus->getSpace()->getAllEquipmentsByName($example['equipmentToAdd'])->isEmpty(),
            message: 'Space should not contain the equipment to add'
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
            [
                'project' => ProjectName::HYDROPONIC_INCUBATOR->value,
                'equipment' => EquipmentEnum::HYDROPONIC_INCUBATOR,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::HYDROPONIC_GARDEN],
            ],
            [
                'project' => ProjectName::BEAT_BOX->value,
                'equipment' => 'blueprint',
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::NEXUS],
            ],
            [
                'project' => ProjectName::ANABOLICS->value,
                'equipment' => GameRationEnum::ANABOLIC,
                'quantity' => 4,
                'creationPlaces' => [RoomEnum::LABORATORY],
            ],
            [
                'project' => ProjectName::SUPER_CALCULATOR->value,
                'equipment' => EquipmentEnum::CALCULATOR,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::NEXUS],
            ],
            [
                'project' => ProjectName::RETRO_FUNGAL_SERUM->value,
                'equipment' => ToolItemEnum::RETRO_FUNGAL_SERUM,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::LABORATORY],
            ],
            [
                'project' => ProjectName::CREATE_MYCOSCAN->value,
                'equipment' => EquipmentEnum::MYCOSCAN,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::LABORATORY],
            ],
            [
                'project' => ProjectName::NARCOTICS_DISTILLER->value,
                'equipment' => EquipmentEnum::NARCOTIC_DISTILLER,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::MEDLAB],
            ],
            [
                'project' => ProjectName::NCC_CONTACT_LENSES->value,
                'equipment' => GearItemEnum::NCC_LENS,
                'quantity' => 2,
                'creationPlaces' => [RoomEnum::LABORATORY],
            ],
            [
                'project' => ProjectName::SPORE_SUCKER->value,
                'equipment' => ToolItemEnum::SPORE_SUCKER,
                'quantity' => 1,
                'creationPlaces' => [RoomEnum::LABORATORY],
            ],
            [
                'project' => ProjectName::MYCOALARM->value,
                'equipment' => ItemEnum::MYCO_ALARM,
                'quantity' => 5,
                'creationPlaces' => [RoomEnum::LABORATORY],
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
                'project' => ProjectName::APERO_KITCHEN->value,
                'equipmentToRemove' => EquipmentEnum::KITCHEN,
                'equipmentToAdd' => EquipmentEnum::SNC_KITCHEN,
            ],
            [
                'project' => ProjectName::MUSHICIDE_SOAP->value,
                'equipmentToRemove' => GearItemEnum::SOAP,
                'equipmentToAdd' => GearItemEnum::SUPER_SOAPER,
            ],
            [
                'project' => ProjectName::NATAMY_RIFLE->value,
                'equipmentToRemove' => ItemEnum::BLASTER,
                'equipmentToAdd' => ItemEnum::NATAMY_RIFLE,
            ],
        ];
    }

    private function shouldReplaceEquipmentOnlyInSpecifiedPlaceAndQuantityDataProvider(): array
    {
        return [
            [
                'project' => ProjectName::NATAMY_RIFLE->value,
                'equipmentToRemove' => ItemEnum::BLASTER,
                'equipmentToAdd' => ItemEnum::NATAMY_RIFLE,
                'place' => RoomEnum::LABORATORY,
                'quantity' => 1,
            ],
        ];
    }
}
