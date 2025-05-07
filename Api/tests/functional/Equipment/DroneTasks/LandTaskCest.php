<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\DroneTasks;

use Mush\Equipment\DroneTasks\LandTask;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class LandTaskCest extends AbstractFunctionalTest
{
    private LandTask $task;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    private Drone $drone;
    private GameEquipment $patrolShip;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->task = $I->grabService(LandTask::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->createExtraPlace(RoomEnum::ALPHA_BAY, $I, $this->daedalus);
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $this->givenAPatrolShipInItsPlace($I);
        $this->givenADroneInPatrolShipPlace($I);
    }

    public function shouldNotBeAvailableIfDroneIsNotAPilot(FunctionalTester $I): void
    {
        $this->whenIExecuteLandTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeAvailableIfLandActionIsNotAvailable(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenPatrolShipIsBroken();

        $this->whenIExecuteLandTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeAvailableIfHuntersAreAttacking(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenSomeHuntersAreAttacking();

        $this->whenIExecuteLandTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfDroneIsNotInAPatrolShip(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenDroneIsNotInAPatrolShip($I);

        $this->whenIExecuteLandTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldMoveDroneToPatrolShipDockingPlace(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->whenIExecuteLandTask();

        $this->thenDroneShouldBeInPatrolShipDockingPlace($I);
    }

    public function shouldMovePatrolShipToItsDockingPlace(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->whenIExecuteLandTask();

        $this->thenPatrolShipShouldBeInItsDockingPlace($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->whenIExecuteLandTask();

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlaceByNameOrThrow(RoomEnum::ALPHA_BAY)->getName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DRONE_LAND,
            ]
        );

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'Le Patrouilleur de **Robo Wheatley #0** vient d\'atterrir.',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_LAND,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldMovePlayersToTheDockingPlace(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenChunIsInThePatrolShipPlace();

        $this->whenIExecuteLandTask();

        $this->thenChunShouldBeInTheDockingPlace($I);
    }

    private function givenADroneInPatrolShipPlace(FunctionalTester $I): void
    {
        $drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN),
            reasons: [],
            time: new \DateTime()
        );

        $I->assertInstanceOf(Drone::class, $drone);
        $this->drone = $drone;
        $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::ELECTRIC_CHARGES,
            holder: $this->drone,
        );
        $this->setupDroneNicknameAndSerialNumber($this->drone, 0, 0);
    }

    private function givenAPatrolShipInItsPlace(FunctionalTester $I): void
    {
        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP]);
        $this->patrolShip = $this->gameEquipmentService->createGameEquipment(
            equipmentConfig: $patrolShipConfig,
            holder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::ALPHA_BAY),
            reasons: [],
            time: new \DateTime(),
            patrolShipName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
        );

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->patrolShip,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN),
            time: new \DateTime(),
        );
    }

    private function givenDroneIsAPilot(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
            holder: $this->drone,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenPatrolShipIsBroken(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $this->patrolShip,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenSomeHuntersAreAttacking(): void
    {
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function givenDroneIsNotInAPatrolShip(FunctionalTester $I): void
    {
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
        );

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->patrolShip,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
        );
    }

    private function givenChunIsInThePatrolShipPlace(): void
    {
        $this->chun->changePlace($this->daedalus->getPlaceByNameOrThrow(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN));
    }

    private function whenIExecuteLandTask(): void
    {
        $this->task->execute($this->drone, new \DateTime());
    }

    private function thenTaskShouldNotBeApplicable(FunctionalTester $I): void
    {
        $I->assertFalse($this->task->isApplicable());
    }

    private function thenDroneShouldBeInPatrolShipDockingPlace(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::ALPHA_BAY,
            actual: $this->drone->getPlace()->getName(),
        );
    }

    private function thenPatrolShipShouldBeInItsDockingPlace(FunctionalTester $I): void
    {
        $I->assertEquals(RoomEnum::ALPHA_BAY, $this->patrolShip->getPlace()->getName());
    }

    private function thenChunShouldBeInTheDockingPlace(FunctionalTester $I): void
    {
        $I->assertEquals(RoomEnum::ALPHA_BAY, $this->chun->getPlace()->getName());
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
