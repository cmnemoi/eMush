<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\DroneTasks;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Mush\Equipment\DroneTasks\TakeoffTask;
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

        $this->givenADroneInRoom($I);
        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
    }

    public function shouldNotBeApplicableIfDroneIsNotAPilot(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenPatrolShipInRoom($I);

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
        $this->givenPatrolShipInRoom($I);

        $this->givenDroneIsPilot();

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    #[DataProvider('nonDaedalusPlaces')]
    public function shouldNotBeApplicableIfDroneNotOnDaedalus(FunctionalTester $I, Example $example): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        // Given that the drone is not in a patrol ship
        $place = $this->daedalus->getPlaceByName($example['place']) ?? $this->createExtraPlace($example['place'], $I, $this->daedalus);
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $this->drone,
            newHolder: $place,
        );

        $this->whenIExecuteTakeoffTask();

        $this->thenTaskShouldNotBeApplicable($I);
    }

    public function shouldMovePatrolShipToItsPlace(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom($I);

        $this->whenIExecuteTakeoffTask();

        $this->thenPatrolShipShouldMoveToItsPlace($I);
    }

    public function shouldMoveDroneToPatrolShipPlace(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom($I);

        $this->whenIExecuteTakeoffTask();

        $this->thenDroneShouldMoveToPatrolShipPlace($I);
    }

    public function shouldPrintPublicLog(FunctionalTester $I): void
    {
        $this->givenThereIsAttackingHunters();

        $this->givenDroneIsPilot();

        $this->givenPatrolShipInRoom($I);

        $this->whenIExecuteTakeoffTask();

        $I->seeInRepository(
            entity: RoomLog::class,
            params: [
                'place' => $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY)->getName(),
                'visibility' => VisibilityEnum::PUBLIC,
                'log' => LogEnum::DRONE_TAKEOFF,
            ]
        );

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

    public function nonDaedalusPlaces(): array
    {
        return [
            ['place' => RoomEnum::PATROL_SHIP_ALPHA_TAMARIN],
            ['place' => RoomEnum::PLANET],
            ['place' => RoomEnum::SPACE],
            ['place' => RoomEnum::PLANET_DEPTHS],
            ['place' => RoomEnum::TABULATRIX_QUEUE],
        ];
    }

    private function givenADroneInRoom(FunctionalTester $I): void
    {
        $drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->chun->getPlace(),
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
        $patrolShipConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PATROL_SHIP]);
        $this->patrolShip = $this->gameEquipmentService->createGameEquipment(
            equipmentConfig: $patrolShipConfig,
            holder: $this->drone->getPlace(),
            reasons: [],
            time: new \DateTime(),
            patrolShipName: EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
        );

        $this->createExtraPlace(RoomEnum::PATROL_SHIP_ALPHA_TAMARIN, $I, $this->daedalus);
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
            equipmentName: EquipmentEnum::PATROL_SHIP,
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
