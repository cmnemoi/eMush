<?php

declare(strict_types=1);

namespace Mush\tests\functional\Equipment\DroneTasks;

use Mush\Action\Entity\ActionConfig;
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
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\LogEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;
use Mush\Tests\RoomLogDto;

/**
 * @internal
 */
final class DroneCest extends AbstractFunctionalTest
{
    private DroneTasksHandler $droneTasksHandler;

    private Drone $drone;
    private GameEquipment $patrolShip;

    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);

        $this->droneTasksHandler = $I->grabService(DroneTasksHandler::class);

        $this->eventService = $I->grabService(EventServiceInterface::class);
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

    public function firefighterShouldExtinguishFire(FunctionalTester $I): void
    {
        $this->givenFireInTheRoom();
        $this->givenDroneIsFirefighter();
        $this->givenDroneHas100PercentChanceToExtinguishFire($I);

        $this->whenDroneActs();

        $this->thenFireShouldBeExtinguished($I);
    }

    public function firefighterShouldPrintAPublicLogWhenFireIsExtinguished(FunctionalTester $I): void
    {
        $this->givenFireInTheRoom();

        $this->givenDroneIsFirefighter();

        $this->givenDroneHas100PercentChanceToExtinguishFire($I);

        $this->whenDroneActs();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':fires: **Robo Wheatley #0** a éteint l\'incendie !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_EXTINGUISHED_FIRE,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function turboShouldExtinguishThenMove(FunctionalTester $I): void
    {
        $this->givenFireInTheRoom();
        $this->givenDroneIsFirefighter();
        $this->givenDroneHas100PercentChanceToExtinguishFire($I);
        $this->givenDroneHasTurboUpgrade();
        $this->givenFrontCorridorExists($I);

        $this->whenDroneActs();

        $this->thenFireShouldBeExtinguished($I);
        $this->thenDroneShouldMoveToFrontCorridor($I);
    }

    public function turboShouldPrintLog(FunctionalTester $I): void
    {
        $this->givenFireInTheRoom();
        $this->givenDroneIsFirefighter();
        $this->givenDroneHasTurboUpgrade();

        $this->whenDroneActs();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: 'La compétence **Turbo** de **Robo Wheatley #0** a porté ses fruits...',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_TURBO_WORKED,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function turboShouldAbortGracefullyIfNoneTaskIsApplicable(FunctionalTester $I): void
    {
        $this->givenDroneHasTurboUpgrade();

        $this->whenDroneActs();

        $I->expect('No infinite loop.');
    }

    public function pilotShouldTakeOff(FunctionalTester $I): void
    {
        $this->givenThereIsOneAttackingHunter();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom($I);

        $this->whenDroneActs();

        $this->thenDroneShouldBeInPatrolShipPlace($I);
    }

    public function pilotShouldShootAtHunter(FunctionalTester $I): void
    {
        $this->givenThereIsOneAttackingHunter();

        $this->givenDroneIsPilot();

        $this->givenDroneIsInAPatrolShip($I);

        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseDamageRange([1 => 1]);

        $this->whenDroneActs();

        $this->thenHunterShouldBeShot($I);
    }

    public function pilotShouldLand(FunctionalTester $I): void
    {
        $this->givenDroneIsPilot();

        $this->givenDroneIsInAPatrolShip($I);

        $this->createExtraPlace(placeName: RoomEnum::ALPHA_BAY, I: $I, daedalus: $this->daedalus);

        $this->whenDroneActs();

        $this->thenDroneShouldBeInPatrolShipDockingPlace($I);
    }

    public function turboShouldMoveThenRepair(FunctionalTester $I): void
    {
        $this->givenDroneHasTurboUpgrade();
        $this->givenFrontCorridorExists($I);
        $mycoscan = $this->givenBrokenMycoscanInFrontCorridor($I);
        $this->givenDroneHas100PercentChanceToRepairEquipment($mycoscan);

        $this->whenDroneActs();

        $this->thenDroneShouldBeInFrontCorridor($I);
        $this->thenEquipmentShouldBeRepaired($mycoscan, $I);
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

    private function givenFrontCorridorExists(FunctionalTester $I): void
    {
        $frontCorridor = $this->createExtraPlace(RoomEnum::FRONT_CORRIDOR, $I, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $frontCorridor);
    }

    private function givenFireInTheRoom(): void
    {
        $this->statusService->createStatusFromName(
            statusName: StatusEnum::FIRE,
            holder: $this->chun->getPlace(),
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenDroneHas100PercentChanceToExtinguishFire(FunctionalTester $I): void
    {
        $extinguishActionConfig = $I->grabEntityFromRepository(ActionConfig::class, ['name' => ActionEnum::EXTINGUISH->value]);
        $extinguishActionConfig->setSuccessRate(100);
        $I->haveInRepository($extinguishActionConfig);
    }

    private function givenDroneHasTurboUpgrade(): void
    {
        $status = $this->statusService->createOrIncrementChargeStatus(
            name: EquipmentStatusEnum::TURBO_DRONE_UPGRADE,
            holder: $this->drone,
        );
        $this->statusService->updateCharge(
            chargeStatus: $status,
            delta: 100,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenDroneIsFirefighter(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE,
            holder: $this->drone,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenThereIsOneAttackingHunter(): void
    {
        $this->daedalus->setHunterPoints(15);
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
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

    private function givenPatrolShipInRoom(FunctionalTester $I): void
    {
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->drone->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
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

    private function givenBrokenMycoscanInFrontCorridor(): GameEquipment
    {
        $mycoscan = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::MYCOSCAN,
            equipmentHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::FRONT_CORRIDOR),
            reasons: [],
            time: new \DateTime(),
        );
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $mycoscan,
            tags: [],
            time: new \DateTime(),
        );

        return $mycoscan;
    }

    private function whenDroneActs(): void
    {
        $this->droneTasksHandler->execute($this->drone, new \DateTime());
    }

    private function thenEquipmentShouldBeRepaired(GameEquipment $equipment, FunctionalTester $I): void
    {
        $I->assertFalse($equipment->hasStatus(EquipmentStatusEnum::BROKEN), \sprintf('Equipment %s should not be broken', $equipment->getLogName()));
    }

    private function thenFireShouldBeExtinguished(FunctionalTester $I): void
    {
        $I->assertFalse($this->chun->getPlace()->hasStatus(StatusEnum::FIRE));
    }

    private function thenDroneShouldBeInPatrolShipPlace(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::PATROL_SHIP_ALPHA_TAMARIN,
            actual: $this->drone->getPlace()->getName(),
        );
    }

    private function thenHunterShouldBeShot(FunctionalTester $I): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $I->assertLessThan(
            expected: $hunter->getHunterConfig()->getInitialHealth(),
            actual: $hunter->getHealth(),
        );
    }

    private function thenDroneShouldBeInPatrolShipDockingPlace(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::ALPHA_BAY,
            actual: $this->drone->getPlace()->getName(),
        );
    }

    private function thenDroneShouldMoveToFrontCorridor(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::FRONT_CORRIDOR,
            actual: $this->drone->getPlace()->getName(),
        );
    }

    private function thenDroneShouldBeInFrontCorridor(FunctionalTester $I): void
    {
        $I->assertEquals(
            expected: RoomEnum::FRONT_CORRIDOR,
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
