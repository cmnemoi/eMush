<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\DroneTasks;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DroneCest extends AbstractFunctionalTest
{
    private DroneTasksHandler $droneTasksHandler;

    private Drone $drone;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->droneTasksHandler = $I->grabService(DroneTasksHandler::class);

        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given a support drone in the room
        $this->drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );

        $this->setupDroneNicknameAndSerialNumber($this->drone, 0, 0);

        // given it has one charge
        $this->statusService->updateCharge(
            chargeStatus: $this->drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            delta: 1,
            tags: [],
            time: new \DateTime(),
        );
    }

    public function shouldRepairEquipmentAfterASuccessfulAttempt(FunctionalTester $I): void
    {
        // given a broken Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        // given drone has a 100% chance to repair the equipment
        $mycoscan->getActionConfigByNameOrThrow(ActionEnum::REPAIR)->setSuccessRate(100);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then the Mycoscan should be repaired
        $I->assertFalse($mycoscan->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function shouldNotRepairEquipmentAfterAFailedAttempt(FunctionalTester $I): void
    {
        // given a broken Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        // given drone has a 0% chance to repair the equipment
        $mycoscan->getActionConfigByNameOrThrow(ActionEnum::REPAIR)->setSuccessRate(0);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then the Mycoscan should still be broken
        $I->assertTrue($mycoscan->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function shouldMoveInARandomMoveIfThereIsNoBrokenEquipment(FunctionalTester $I): void
    {
        // given front corridor exists
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then drone support should be in front corridor
        $I->assertEquals($frontCorridor->getLogName(), $this->drone->getPlace()->getLogName());
    }

    public function shouldNotMoveIfThereIsABrokenEquipmentToRepair(FunctionalTester $I): void
    {
        // given front corridor
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);

        // given a broken Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then drone should still be in Chun's room
        $I->assertEquals(
            expected: $this->chun->getPlace()->getLogName(),
            actual: $this->drone->getPlace()->getLogName()
        );
    }

    public function shouldCreateExitLogWhenMoving(FunctionalTester $I): void
    {
        // given front corridor exists
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then there should be a public log telling drone exited Chun's room
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'log' => LogEnum::DRONE_EXITED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldCreateEnterLogWhenMoving(FunctionalTester $I): void
    {
        // given front corridor exists
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then there should be a public log telling drone entered front corridor
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $frontCorridor->getLogName(),
                'log' => LogEnum::DRONE_ENTERED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldHaveProperlyParametrizedExitLogWhenMoving(FunctionalTester $I): void
    {
        // given front corridor exists
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then the log should contain drone's nickname and serial number
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'log' => LogEnum::DRONE_EXITED_ROOM,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: [
                'drone' => '**Robo Wheatley #0**',
                'drone_nickname' => 0,
                'drone_serial_number' => 0,
            ],
            actual: $roomLog->getParameters()
        );
    }

    public function shouldCreateAPublicLogWhenRepairingEquipment(FunctionalTester $I): void
    {
        // given a broken Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        // given drone has a 100% chance to repair the equipment
        $mycoscan->getActionConfigByNameOrThrow(ActionEnum::REPAIR)->setSuccessRate(100);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then there should be a public log telling drone repaired Mycoscan
        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'log' => LogEnum::DRONE_REPAIRED_EQUIPMENT,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );
    }

    public function shouldHaveProperlyParametrizedRepairLogWhenRepairingEquipment(FunctionalTester $I): void
    {
        // given a broken Mycoscan in the room
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        // given drone has a 100% chance to repair the equipment
        $mycoscan->getActionConfigByNameOrThrow(ActionEnum::REPAIR)->setSuccessRate(100);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then the log should contain drone's nickname and serial number
        $roomLog = $I->grabEntityFromRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->chun->getPlace()->getLogName(),
                'log' => LogEnum::DRONE_REPAIRED_EQUIPMENT,
                'visibility' => VisibilityEnum::PUBLIC,
            ]
        );

        $I->assertEquals(
            expected: '**Robo Wheatley #0**',
            actual: $roomLog->getParameters()['drone']
        );

        $I->assertEquals(
            expected: 'mycoscan',
            actual: $roomLog->getParameters()['target_equipment']
        );
    }

    public function shouldRepairBrokenPatrolShip(FunctionalTester $I): void
    {
        // given a broken Patrol Ship in the room
        $patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $patrolShip,
            tags: [],
            time: new \DateTime(),
        );

        // given drone has a 100% chance to repair the equipment
        $patrolShip->getActionConfigByNameOrThrow(ActionEnum::RENOVATE)->setSuccessRate(100);

        // when drone acts
        $this->droneTasksHandler->execute($this->drone, new \DateTime());

        // then the Patrol Ship should be repaired
        $I->assertFalse($patrolShip->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function shouldRepairBrokenDoor(FunctionalTester $I): void
    {
        $door = $this->givenABrokenDoor($I);
        $this->givenDroneHas100PercentChanceToRepairEquipment($door);

        $this->whenDroneActs();

        $this->thenEquipmentShouldBeRepaired($door, $I);
    }

    private function givenABrokenDoor(FunctionalTester $I): Door
    {
        $door = Door::createFromRooms(
            $this->chun->getPlace(),
            $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus)
        );
        $door->setEquipment($I->grabEntityFromRepository(entity: EquipmentConfig::class, params: ['name' => 'door_default']));
        $I->haveInRepository($door);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $door,
            tags: [],
            time: new \DateTime(),
        );

        return $door;
    }

    private function givenDroneHas100PercentChanceToRepairEquipment(GameEquipment $equipment): void
    {
        $equipment->getActionConfigByNameOrThrow(ActionEnum::REPAIR)->setSuccessRate(100);
    }

    private function whenDroneActs(): void
    {
        $this->droneTasksHandler->execute($this->drone, new \DateTime());
    }

    private function thenEquipmentShouldBeRepaired(GameEquipment $equipment, FunctionalTester $I): void
    {
        $I->assertFalse($equipment->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
