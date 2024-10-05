<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\DroneTasks;

use Mush\Equipment\DroneTasks\TakeoffTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class TakeoffTaskCest extends AbstractFunctionalTest
{
    private TakeoffTask $task;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Drone $drone;
    private GameEquipment $patrolShip;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->task = $I->grabService(TakeoffTask::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenADroneInRoom();
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
    }

    public function shouldNotBeApplicableIfDroneIsNotAPilot(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenPatrolShipInRoom();

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfNoPatrolShipInRoom(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfThereIsNoAttackingHunters(FunctionalTester $I): void
    {
        $this->givenPatrolShipInRoom();

        $this->givenDroneIsPilot();

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfDroneIsInAPatrolShip(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenDroneIsInAPatrolShip($I);

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldMovePatrolShipToItsPlace(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom();

        $this->whenIExecuteTakeoffTask();

        $this->thenPatrolShipShouldMoveToItsPlace($I);
    }

    public function shouldMoveDroneToPatrolShipPlace(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom();

        $this->whenIExecuteTakeoffTask();

        $this->thenDroneShouldMoveToPatrolShipPlace($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom();

        $this->whenIExecuteTakeoffTask();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Robo Wheatley #0** vient de décoller.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_TAKEOFF,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function givenADroneInRoom(): void
    {
        $this->drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::ELECTRIC_CHARGES,
            holder: $this->drone,
        );
        $this->setupDroneNicknameAndSerialNumber($this->drone, 0, 0);
    }

    private function givenDroneIsPilot(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
            holder: $this->drone,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPatrolShipInRoom(): void
    {
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->drone->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
    }

    private function givenThereIsAttackingHunters(): void
    {
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function givenDroneIsInAPatrolShip(FunctionalTester $I): void
    {
        $place = $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $place,
            time: new \DateTime(),
        );
    }

    private function whenIExecuteTakeoffTask(): void
    {
        $this->task->execute($this->drone, new \DateTime());
    }

    private function thenTaskShouldNotBeApplicable(FunctionalTester $I): void
    {
        $I->assertFalse($this->task->isApplicable());
    }

    private function thenPatrolShipShouldMoveToItsPlace(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::PATROL_SHIP_ALPHA_TAMARIN,
            actual: $this->patrolShip->getPlace()->getName(),
        );
    }

    private function thenDroneShouldMoveToPatrolShipPlace(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::PATROL_SHIP_ALPHA_TAMARIN,
            actual: $this->drone->getPlace()->getName(),
        );
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
