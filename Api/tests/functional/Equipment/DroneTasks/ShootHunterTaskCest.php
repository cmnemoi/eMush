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
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterPoolEvent;
use Mush\Hunter\Event\HunterVariableEvent;
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

    private Drone $drone;
    private GameEquipment $patrolShip;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->task = $I->grabService(ShootHunterTask::class);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);

        $this->givenAPatrolShipInBattle();
        $this->givenADroneInPatrolShip();
    }

    public function shouldNotBeApplicableIfDroneIsNotAPilot(FunctionalTester $I): void
    {
        $this->givenOneAttackingHunter();

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldNotBeApplicableIfNoShootHunterActionIsAvailable(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        // drone is not in patrol ship
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $this->daedalus->getPlaceByNameOrThrow(RoomEnum::SPACE),
        );

        $this->whenIExecuteShootHunterTask();

        $this->thenTaskShouldNotBeApplicable($I);
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

    public function shouldRemoveHealthToAttackingHunter(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->whenIExecuteShootHunterTask();

        $this->thenAttackingHunterShouldHaveLessHealth($I);
    }

    public function shouldPrintAPublicLogWhenHittingAHunter(FunctionalTester $I): void
    {
        $this->givenDroneIsAPilot();

        $this->givenOneAttackingHunter();

        $this->whenIExecuteShootHunterTask();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: "C'était presque ça ! **Robo Wheatley #0** a touché un **Hunter**.",
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

        $this->whenIExecuteShootHunterTask();

        $this->ISeeTranslatedRoomLogInRepository(
            expectedRoomLog: '**Robo Wheatley #0** émet une trille de bonheur ! Un **Hunter** de moins, *bli bli blip* !',
            actualRoomLogDto: new RoomLogDto(
                player: $this->chun,
                log: LogEnum::DRONE_KILL_HUNTER,
                visibility: VisibilityEnum::PUBLIC,
                inPlayerRoom: false,
            ),
            I: $I,
        );
    }

    private function givenAPatrolShipInBattle(): void
    {
        $this->patrolShip = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
        $this->patrolShip->getWeaponMechanicOrThrow()->setBaseAccuracy(100);
    }

    private function givenADroneInPatrolShip(): void
    {
        $this->drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->patrolShip->getPlace(),
            reasons: [],
            time: new \DateTime()
        );
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
        $hunter = $this->daedalus->getAttackingHunters()->first();
        $hunterVariableEvent = new HunterVariableEvent(
            hunter: $hunter,
            variableName: HunterVariableEnum::HEALTH,
            quantity: -5,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterVariableEvent, HunterVariableEvent::CHANGE_VARIABLE);
    }

    private function whenIExecuteShootHunterTask(): void
    {
        $this->task->execute($this->drone, new \DateTime());
    }

    private function thenTaskShouldNotBeApplicable(FunctionalTester $I): void
    {
        $I->assertFalse($this->task->isApplicable());
    }

    private function thenAttackingHunterShouldHaveLessHealth(FunctionalTester $I): void
    {
        $hunter = $this->daedalus->getAttackingHunters()->first();

        $I->assertLessThan(6, $hunter->getHealth());
    }

    private function setupDroneNicknameAndSerialNumber(Drone $drone, int $nickName, int $serialNumber): void
    {
        $droneInfo = $drone->getDroneInfo();
        $ref = new \ReflectionClass($droneInfo);
        $ref->getProperty('nickName')->setValue($droneInfo, $nickName);
        $ref->getProperty('serialNumber')->setValue($droneInfo, $serialNumber);
    }
}
