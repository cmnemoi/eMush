<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\DroneTasks;

use Mush\Equipment\DroneTasks\ShootHunterTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Hunter\Service\CreateHunterService;
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
final class ShootHunterTaskCest extends AbstractFunctionalTest
{
    private ShootHunterTask $task;
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private CreateHunterService $createHunter;

    private Drone $drone;
    private GameEquipment $patrolShip;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->task = $I->grabService(ShootHunterTask::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
        $this->createHunter = $I->grabService(CreateHunterService::class);

        $this->givenAPatrolShipInBattle($I);
        $this->givenADroneInPatrolShip($I);
    }

    public function shouldNotBeApplicableIfDroneIsNotAPilot(FunctionalTester $I): void
    {
        $this->givenOneAttackingHunter();

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldThrowIfDroneNotInAPatrolShip(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        // Given that the drone is not in a patrol ship
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE),
        );

        $I->expectThrowable(
            new \RuntimeException('There should be a patrol ship equipment in the place'),
            function () {
                $this->whenIExecuteShootHunterTask();
            },
        );
    }

    public function shouldNotBeApplicableIfPatrolShipIsNotOperational(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->givenPatrolShipIsUncharged();

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfThereIsNoAttackingHunters(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfDroneIsNotInAPatrolShip(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        // drone is not in patrol ship
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
        );

        // patrol ship is not in its place
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->patrolShip,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
        );

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldRemoveHealthToAttackingHunter(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->givenPatrolShipAlwaysHits();

        $this->givenPatrolShipDealsOnePointOfDamage();

        $this->whenIExecuteShootHunterTask();

        $this->thenAttackingHunterShouldHaveLessHealth($I);
    }

    public function shouldPrintAPublicLogWhenHittingAHunter(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseDamageRange([1 => 1]);

        $this->whenIExecuteShootHunterTask();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ":hunter: C'était presque ça ! **Robo Wheatley #0** a touché un **Hunter**.",
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_HIT_HUNTER,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldPrintAPublicLogWhenKillingAHunter(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->givenHunterHasOneHealthPoint();

        $this->givenPatrolShipAlwaysHits();

        $this->givenPatrolShipDealsOnePointOfDamage();

        $this->whenIExecuteShootHunterTask();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: ':hunter: **Robo Wheatley #0** émet un trille de bonheur ! Un **Hunter** de moins, *bli bli blip* !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_KILL_HUNTER,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    public function shouldConsumeOnePatrolShipChargeOnSuccessfulShot(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->givenPatrolShipAlwaysHits();

        $this->whenIExecuteShootHunterTask();

        $this->thenPatrolShipShouldHaveCharges(9, $I);
    }

    public function shouldConsumeOnePatrolShipChargeOnFailedShot(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->givenPatrolShipAlwaysMisses();

        $this->givenPatrolShipDealsOnePointOfDamage();

        $this->whenIExecuteShootHunterTask();

        $this->thenPatrolShipShouldHaveCharges(9, $I);
    }

    public function shouldNotBeApplicableIfOnlyTransportAreAroundDaedalus(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneTransport();

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    private function givenAPatrolShipInBattle(FunctionalTester $I): void
    {
        $place = $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP,
            equipmentHolder: $place,
            reasons: [],
            time: new \DateTime()
        );
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenADroneInPatrolShip(FunctionalTester $I): void
    {
        $drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->patrolShip->getPlace(),
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

    private function givenDroneIsAPilot(): void
    {
        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
            holder: $this->drone,
            tags: [],
            time: new \DateTime(),
        );
    }

    private function givenOneAttackingHunter(): void
    {
        $this->daedalus->setHunterPoints(15);
        $hunterPoolEvent = new HunterPoolEvent(
            daedalus: $this->daedalus,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterPoolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
    }

    private function givenPatrolShipIsUncharged(): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $this->patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            delta: 0,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenHunterHasOneHealthPoint(): void
    {
        $hunter = $this->daedalus->getHuntersAroundDaedalus()->first();
        $hunterVariableEvent = new HunterVariableEvent(
            hunter: $hunter,
            variableName: HunterVariableEnum::HEALTH,
            quantity: -5,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function givenPatrolShipAlwaysHits(): void
    {
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenPatrolShipDealsOnePointOfDamage(): void
    {
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseDamageRange([1 => 1, 2 => 1]);
    }

    private function givenPatrolShipAlwaysMisses(): void
    {
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(0);
    }

    private function givenOneTransport(): void
    {
        $this->createHunter->execute(HunterEnum::TRANSPORT, $this->daedalus->getId());
    }

    private function whenIExecuteShootHunterTask(): void
    {
        $this->task->execute($this->drone, new \DateTime());
    }

    private function thenTaskShouldNotBeApplicable(FunctionalTester $I): void
    {
        $I->assertFalse($this->task->isApplicable(), 'Task should not be applicable');
    }

    private function thenAttackingHunterShouldHaveLessHealth(FunctionalTester $I): void
    {
        $hunter = $this->daedalus->getHuntersAroundDaedalus()->first();

        $I->assertLessThan(6, $hunter->getHealth());
    }

    private function thenPatrolShipShouldHaveCharges(int $expectedCharges, FunctionalTester $I): void
    {
        $I->assertEquals($expectedCharges, $this->patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge());
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
