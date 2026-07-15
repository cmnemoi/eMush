<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\NPCTasks;

use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\NPCTasks\AiHandler\ChickenTasksHandler;
use Mush\Equipment\NPCTasks\Chicken\LaySpaceCapsuleTask;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlaceStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class ChickenCest extends AbstractFunctionalTest
{
    private ChickenTasksHandler $chickenTasksHandler;
    private LaySpaceCapsuleTask $laySpaceCapsuleTask;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Place $corridor;

    private GameEquipment $chicken;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->chickenTasksHandler = $I->grabService(ChickenTasksHandler::class);
        $this->laySpaceCapsuleTask = $I->grabService(LaySpaceCapsuleTask::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->corridor = $this->createExtraPlace(RoomEnum::REAR_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->chun->getPlace(), $this->corridor);

        $this->chicken = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::TREASURE_HUNT_SPACE_CHICKEN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->laySpaceCapsuleTask->setLaySpaceCapsuleChance(100);
    }

    public function shouldCreateLogsWhenMoving(FunctionalTester $I): void
    {
        $this->whenChickenActs();

        $this->thenISeeEnteredAndLeftRoomLogsInRepositories($I);
    }

    public function shouldLayASpaceCapsuleInItsRoom(FunctionalTester $I): void
    {
        $this->whenChickenLaysACapsule();

        $I->assertTrue($this->chicken->getPlace()->hasEquipmentByName(ToolItemEnum::SPACE_CAPSULE));
    }

    public function shouldNotTrapRoomIfNotInfected(FunctionalTester $I): void
    {
        $this->whenChickenLaysACapsule();

        $I->assertFalse($this->chicken->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }

    public function shouldTrapRoomIfInfected(FunctionalTester $I): void
    {
        $this->givenChickenIsInfectedBy($this->chun);

        $this->whenChickenLaysACapsule();

        $I->assertTrue($this->chicken->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
        $I->assertTrue($this->chicken->getPlace()->hasStatus(PlaceStatusEnum::CHICKEN_TRAPPED->value));
    }

    public function shouldNotTrapRoomTwiceIfAlreadyTrapped(FunctionalTester $I): void
    {
        $this->givenChickenIsInfectedBy($this->chun);

        $this->whenChickenLaysACapsule();
        $this->whenChickenLaysACapsule();

        $I->assertTrue($this->chicken->getPlace()->hasStatus(PlaceStatusEnum::MUSH_TRAPPED->value));
    }

    public function shouldNotLayASpaceCapsuleWhenChanceFails(FunctionalTester $I): void
    {
        $this->laySpaceCapsuleTask->setLaySpaceCapsuleChance(0);

        $this->whenChickenLaysACapsule();

        $I->assertFalse($this->chicken->getPlace()->hasEquipmentByName(ToolItemEnum::SPACE_CAPSULE));
    }

    private function givenChickenIsInfectedBy(Player $player): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::CHICKEN_INFECTED,
            holder: $this->chicken,
            tags: [],
            time: new \DateTime(),
            target: $player,
        );
    }

    private function whenChickenActs(): void
    {
        $this->chickenTasksHandler->execute($this->chicken, new \DateTime());
    }

    private function whenChickenLaysACapsule(): void
    {
        $this->laySpaceCapsuleTask->execute($this->chicken, new \DateTime());
    }

    private function thenISeeEnteredAndLeftRoomLogsInRepositories(FunctionalTester $I): void
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
                'place' => $this->corridor->getLogName(),
                'log' => LogEnum::NPC_ENTERED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }
}
