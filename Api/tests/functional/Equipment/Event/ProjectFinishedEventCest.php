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

    public function shouldReplaceEquipmentWhenProjectIsFinished(FunctionalTester $I): void
    {
        $room = $this->player->getPlace();

        // given I have a Shower in my current room.
        $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::SHOWER,
            equipmentHolder: $room,
            reasons: [],
            time: new \DateTime()
        );

        // when Thalasso project is finished
        $this->finishProject(
            project: $this->daedalus->getProjectByName(ProjectName::THALASSO),
            author: $this->chun,
            I: $I
        );

        // then the room should not contain a shower
        $I->assertTrue(
            condition: $room->getAllEquipmentsByName(EquipmentEnum::SHOWER)->isEmpty()
        );

        // but the room should contain 1 thalasso
        $I->assertCount(
            expectedCount: 1,
            haystack: $room->getAllEquipmentsByName(EquipmentEnum::THALASSO)
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
}
