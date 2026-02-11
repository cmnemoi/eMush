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
use Mush\Place\Entity\Place;
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
        // Given
        $places = $this->givenIHaveEquipmentCreationPlaces($example['creationPlaces'], $I);

        // When
        $this->whenIFinishProject($example['project'], $I);

        // Then
        $this->thenPlacesShouldContainEquipmentInExpectedQuantity($places, $example['equipment'], $example['quantity'], $I);
    }

    #[DataProvider(methodName: 'shouldReplaceEquipmentWhenProjectIsFinishedDataProvider')]
    public function shouldReplaceEquipmentWhenProjectIsFinished(FunctionalTester $I, Example $example): void
    {
        // Given
        $room = $this->givenIHaveEquipmentToReplaceInLaboratory($example['equipmentToRemove'], $I);

        // When
        $this->whenIFinishProject($example['project'], $I);

        // Then
        $this->thenRoomShouldNotContainEquipmentToRemove($room, $example['equipmentToRemove'], $I);
        $this->thenRoomShouldContainEquipmentToAdd($room, $example['equipmentToAdd'], $I);
    }

    #[DataProvider(methodName: 'shouldReplaceEquipmentOnlyInSpecifiedPlaceAndQuantityDataProvider')]
    public function shouldReplaceEquipmentOnlyInSpecifiedPlaceAndQuantity(FunctionalTester $I, Example $example): void
    {
        // Given
        $room = $this->givenIHaveEquipmentToReplaceInRoomTwice($example['place'], $example['equipmentToRemove'], $I);
        $this->givenIHaveEquipmentInSpace($example['equipmentToRemove'], $I);

        // When
        $this->whenIFinishProject($example['project'], $I);

        // Then
        $this->thenRoomShouldStillHaveOneEquipmentToRemove($room, $example['equipmentToRemove'], $I);
        $this->thenRoomShouldContainEquipmentToAdd($room, $example['equipmentToAdd'], $I);

        $this->thenSpaceShouldStillContainEquipmentToRemove($example['equipmentToRemove'], $I);
        $this->thenSpaceShouldNotContainEquipmentToAdd($example['equipmentToAdd'], $I);
    }

    public function shouldReplaceEquipmentInPlayerInventory(FunctionalTester $I): void
    {
        // Given
        $this->givenIHaveEquipmentToReplaceInPlayerInventory(ItemEnum::BLASTER, $I);

        // When
        $this->whenIFinishNatamyRifleProject($I);

        // Then
        $this->thenPlayerShouldNotHaveEquipmentToReplace(ItemEnum::BLASTER, $I);
        $this->thenPlayerShouldHaveEquipmentToAdd(ItemEnum::NATAMY_RIFLE, $I);
    }

    // Given methods
    private function givenIHaveEquipmentCreationPlaces(array $creationPlaces, FunctionalTester $I): array
    {
        $places = [];
        foreach ($creationPlaces as $creationPlace) {
            // if room already exists dont create it twice
            if ($this->daedalus->getPlaceByName($creationPlace)) {
                $places[] = $this->daedalus->getPlaceByNameOrThrow($creationPlace);
            } else {
                $places[] = $this->createExtraPlace(placeName: $creationPlace, I: $I, daedalus: $this->daedalus);
            }
        }

        return $places;
    }

    private function givenIHaveEquipmentToReplaceInLaboratory(string $equipmentToRemove, FunctionalTester $I): Place
    {
        $room = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $equipmentToRemove,
            equipmentHolder: $room,
            reasons: [],
            time: new \DateTime()
        );

        return $room;
    }

    private function givenIHaveEquipmentToReplaceInRoomTwice(string $placeName, string $equipmentToRemove, FunctionalTester $I): Place
    {
        $room = $this->daedalus->getPlaceByNameOrThrow($placeName);

        for ($i = 0; $i < 2; ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                equipmentName: $equipmentToRemove,
                equipmentHolder: $room,
                reasons: [],
                time: new \DateTime()
            );
        }

        return $room;
    }

    private function givenIHaveEquipmentInSpace(string $equipmentName, FunctionalTester $I): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $equipmentName,
            equipmentHolder: $this->daedalus->getSpace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenIHaveEquipmentToReplaceInPlayerInventory(string $equipmentName, FunctionalTester $I): void
    {
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: $equipmentName,
            equipmentHolder: $this->player,
            reasons: [],
            time: new \DateTime()
        );
    }

    // When methods
    private function whenIFinishProject(string $projectName, FunctionalTester $I): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::from($projectName));
        $this->finishProject(
            project: $project,
            author: $this->chun,
            I: $I
        );
    }

    private function whenIFinishNatamyRifleProject(FunctionalTester $I): void
    {
        $project = $this->daedalus->getProjectByName(ProjectName::NATAMY_RIFLE);
        $this->finishProject(
            project: $project,
            author: $this->player,
            I: $I
        );
    }

    // Then methods
    private function thenPlacesShouldContainEquipmentInExpectedQuantity(array $places, string $equipmentName, int $expectedQuantity, FunctionalTester $I): void
    {
        foreach ($places as $place) {
            $I->assertCount(
                expectedCount: $expectedQuantity,
                haystack: $place->getEquipmentsByNames([$equipmentName])
            );
        }
    }

    private function thenRoomShouldNotContainEquipmentToRemove(Place $room, string $equipmentToRemove, FunctionalTester $I): void
    {
        $I->assertTrue(
            condition: $room->getEquipmentsByNames([$equipmentToRemove])->isEmpty()
        );
    }

    private function thenRoomShouldContainEquipmentToAdd(Place $room, string $equipmentToAdd, FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getEquipmentsByNames([$equipmentToAdd])
        );
    }

    private function thenRoomShouldStillHaveOneEquipmentToRemove(Place $room, string $equipmentToRemove, FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getEquipmentsByNames([$equipmentToRemove]),
            message: 'Lab should contain one piece of equipment to remove'
        );
    }

    private function thenSpaceShouldStillContainEquipmentToRemove(string $equipmentToRemove, FunctionalTester $I): void
    {
        $I->assertCount(
            expectedCount: 1,
            haystack: $this->daedalus->getSpace()->getEquipmentsByNames([$equipmentToRemove]),
            message: 'Space should still contain the equipment to remove'
        );
    }

    private function thenSpaceShouldNotContainEquipmentToAdd(string $equipmentToAdd, FunctionalTester $I): void
    {
        $I->assertTrue(
            condition: $this->daedalus->getSpace()->getEquipmentsByNames([$equipmentToAdd])->isEmpty(),
            message: 'Space should not contain the equipment to add'
        );
    }

    private function thenPlayerShouldNotHaveEquipmentToReplace(string $equipmentToRemove, FunctionalTester $I): void
    {
        $I->assertFalse(
            condition: $this->player->hasEquipmentByName($equipmentToRemove),
            message: 'Player should not have blaster (replaced by Natamy Rifle research project)'
        );
    }

    private function thenPlayerShouldHaveEquipmentToAdd(string $equipmentToAdd, FunctionalTester $I): void
    {
        $I->assertTrue(
            $this->player->hasEquipmentByName($equipmentToAdd),
            message: 'Player should have a Natamy Rifle'
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
            [
                'project' => ProjectName::MUSHICIDE_SOAP->value,
                'equipmentToRemove' => GearItemEnum::SOAP,
                'equipmentToAdd' => GearItemEnum::SUPER_SOAPER,
                'place' => RoomEnum::LABORATORY,
                'quantity' => 1,
            ],
        ];
    }
}
