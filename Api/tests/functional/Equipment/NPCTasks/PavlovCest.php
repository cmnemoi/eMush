<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\NPCTasks;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\NPCTasks\AiHandler\DogTasksHandler;
use Mush\Equipment\NPCTasks\Pavlov\AnnoyCatTask;
use Mush\Equipment\NPCTasks\Pavlov\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class PavlovCest extends AbstractFunctionalTest
{
    private DogTasksHandler $dogTasksHandler;
    private AnnoyCatTask $annoyCat;
    private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask;

    private GameEquipment $pavlov;

    private GameEquipmentServiceInterface $gameEquipmentService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->dogTasksHandler = $I->grabService(DogTasksHandler::class);
        $this->annoyCat = $I->grabService(AnnoyCatTask::class);
        $this->moveInRandomAdjacentRoomTask = $I->grabService(MoveInRandomAdjacentRoomTask::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);

        $this->pavlov = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::PAVLOV,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
    }

    public function shouldCreateLogsWhenMoving(FunctionalTester $I)
    {
        $this->givenFrontCorridorExists($I);

        $this->whenPavlovActs();

        $this->thenISeeEnteredAndLeftRoomLogsInRepositories($I);
    }

    private function givenFrontCorridorExists(FunctionalTester $I)
    {
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);
    }

    private function whenPavlovActs()
    {
        $this->dogTasksHandler->execute($this->pavlov, new \DateTime());
    }

    private function thenISeeEnteredAndLeftRoomLogsInRepositories(FunctionalTester $I)
    {
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'log' => LogEnum::NPC_EXITED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlaceByName(RoomEnum::FRONT_CORRIDOR)->getLogName(),
                'log' => LogEnum::NPC_ENTERED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
