<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\DroneTasks;

use Codeception\PHPUnit\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\ConfigData\EquipmentConfigData;
use Mush\Equipment\DroneTasks\MoveTask;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventService;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Hunter\ConfigData\HunterConfigData;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Entity\HunterConfig;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;

/**
 * @internal
 */
final class MoveTaskTest extends TestCase
{
    private MoveTask $task;
    private Daedalus $daedalus;
    private Drone $drone;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private Place $laboratory;
    private Place $medlab;
    private Place $frontCorridor;
    private Place $bridge;
    private \DateTime $time;

    /**
     * @before
     */
    protected function setUp(): void
    {
        /** @var EventService $eventService */
        $eventService = self::createStub(EventService::class);
        $this->gameEquipmentService = \Mockery::spy(GameEquipmentServiceInterface::class);
        $this->time = new \DateTime();

        $this->task = new MoveTask(
            eventService: $eventService,
            statusService: new FakeStatusService(),
            findNextRoomTowardsCondition: new FindNextRoomTowardsConditionService(),
            gameEquipmentService: $this->gameEquipmentService,
            getRandomElementsFromArray: new FakeGetRandomElementsFromArrayService(),
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->createRooms();
        $this->connectRooms();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testShouldMoveRandomlyWhenDroneHasNoSensor(): void
    {
        $this->givenDroneInLaboratory();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldMoveToAdjacentRoomWithBrokenEquipmentWhenDroneHasSensor(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenBrokenEquipmentIn($this->frontCorridor);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveToClosestRoomWithBrokenEquipmentWhenNoAdjacentRoomHasBrokenEquipment(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenBrokenEquipmentIn($this->bridge);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveRandomlyWhenNoRoomHasBrokenEquipmentAndDroneHasSensor(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldMoveToAdjacentRoomOnFireWhenDroneHasSensorAndFirefighter(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasFirefighterUpgrade();
        $this->givenRoomOnFire($this->frontCorridor);
        $this->givenBrokenEquipmentIn($this->medlab);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveToClosestRoomOnFireWhenNoAdjacentRoomOnFire(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasFirefighterUpgrade();
        $this->givenRoomOnFire($this->bridge);
        $this->givenBrokenEquipmentIn($this->medlab);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveToBrokenEquipmentWhenNoFireAndDroneHasSensorAndFirefighter(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasFirefighterUpgrade();
        $this->givenBrokenEquipmentIn($this->frontCorridor);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveRandomlyWhenNoFireAndNoBrokenEquipmentAndDroneHasSensorAndFirefighter(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasFirefighterUpgrade();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldMoveToRoomWithPatrolShipAndAttackingHunterWhenDroneHasSensorAndPilot(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenOperationalPatrolShipIn($this->frontCorridor);
        $this->givenAttackingHunter();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveToClosestRoomWithPatrolShipAndAttackingHunterWhenNoAdjacentRoomHasPatrolShip(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenOperationalPatrolShipIn($this->bridge);
        $this->givenAttackingHunter();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldPreferBrokenEquipmentOverPatrolShipWhenDroneHasSensorAndPilot(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenOperationalPatrolShipIn($this->frontCorridor);
        $this->givenBrokenEquipmentIn($this->medlab);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldPreferFireOverBrokenEquipmentAndPatrolShipWhenDroneHasSensorAndPilotAndFirefighter(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenDroneHasFirefighterUpgrade();
        $this->givenOperationalPatrolShipIn($this->medlab);
        $this->givenBrokenEquipmentIn($this->medlab);
        $this->givenRoomOnFire($this->frontCorridor);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->frontCorridor);
    }

    public function testShouldMoveRandomlyWhenNoOperationalPatrolShipAndDroneHasSensorAndPilot(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenAttackingHunter();

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldMoveRandomlyWhenNoAttackingHunterAndDroneHasSensorAndPilot(): void
    {
        $this->givenDroneInLaboratory();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();
        $this->givenOperationalPatrolShipIn($this->frontCorridor);

        $this->whenIExecuteTask();

        $this->thenDroneShouldMoveTo($this->medlab);
    }

    public function testShouldNotMoveIfInsideAPatrolShipIfDroneHasSensorButNotPilot(): void
    {
        $this->givenDroneInAPatrolShip();
        $this->givenDroneHasSensorUpgrade();
        $this->givenOperationalPatrolShipIn($this->frontCorridor);

        $this->whenIExecuteTask();

        $this->thenDroneShouldNotMove();
    }

    public function testShouldNotMoveIfInsideAPatrolShipIfDroneHasSensorAndPilot(): void
    {
        $this->givenDroneInAPatrolShip();
        $this->givenDroneHasSensorUpgrade();
        $this->givenDroneHasPilotUpgrade();

        $this->whenIExecuteTask();

        $this->thenDroneShouldNotMove();
    }

    private function createRooms(): void
    {
        $this->laboratory = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);
        $this->createMedlab();
        $this->createFrontCorridor();
        $this->createBridge();
    }

    private function createMedlab(): void
    {
        $this->medlab = Place::createRoomByNameInDaedalus(RoomEnum::MEDLAB, $this->daedalus);
        (new \ReflectionClass(Place::class))->getProperty('id')->setValue($this->medlab, 2);
    }

    private function createFrontCorridor(): void
    {
        $this->frontCorridor = Place::createRoomByNameInDaedalus(RoomEnum::FRONT_CORRIDOR, $this->daedalus);
        (new \ReflectionClass(Place::class))->getProperty('id')->setValue($this->frontCorridor, 3);
    }

    private function createBridge(): void
    {
        $this->bridge = Place::createRoomByNameInDaedalus(RoomEnum::BRIDGE, $this->daedalus);
        (new \ReflectionClass(Place::class))->getProperty('id')->setValue($this->bridge, 4);
    }

    /**
     * Laboratory -> Medlab
     * Laboratory -> Front corridor -> Bridge.
     */
    private function connectRooms(): void
    {
        $door = Door::createFromRooms($this->laboratory, $this->medlab);
        $door->setEquipment(EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DOOR)));

        $door = Door::createFromRooms($this->laboratory, $this->frontCorridor);
        $door->setEquipment(EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DOOR)));

        $door = Door::createFromRooms($this->frontCorridor, $this->bridge);
        $door->setEquipment(EquipmentConfig::fromConfigData(EquipmentConfigData::getByEquipmentName(EquipmentEnum::DOOR)));
    }

    private function givenDroneInLaboratory(): void
    {
        $this->drone = GameEquipmentFactory::createDroneForHolder($this->laboratory);
        $this->drone->getChargeStatus()->setCharge(1);
    }

    private function givenDroneHasSensorUpgrade(): void
    {
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::SENSOR_DRONE_UPGRADE,
            $this->drone
        );
    }

    private function givenDroneHasFirefighterUpgrade(): void
    {
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE,
            $this->drone
        );
    }

    private function givenDroneHasPilotUpgrade(): void
    {
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::PILOT_DRONE_UPGRADE,
            $this->drone
        );
    }

    private function givenOperationalPatrolShipIn(Place $room): GameEquipment
    {
        $patrolShip = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::PATROL_SHIP_ALPHA_TAMARIN,
            $room
        );

        $patrolShip->getEquipment()->setMechanics(
            new ArrayCollection([new PatrolShip()])
        );

        return $patrolShip;
    }

    private function givenBrokenEquipmentIn(Place $room): void
    {
        $equipment = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::AUXILIARY_TERMINAL,
            $room
        );
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::BROKEN,
            $equipment
        );
    }

    private function givenRoomOnFire(Place $room): void
    {
        StatusFactory::createStatusByNameForHolder(
            StatusEnum::FIRE,
            $room
        );
    }

    private function givenDroneInAPatrolShip(): void
    {
        $patrolShipRoom = Place::createPatrolShipPlaceForDaedalus(
            name: RoomEnum::PATROL_SHIP_ALPHA_JUJUBE,
            daedalus: $this->daedalus
        );
        (new \ReflectionClass(Place::class))->getProperty('id')->setValue($patrolShipRoom, 5);
        $this->drone = GameEquipmentFactory::createDroneForHolder($patrolShipRoom);
    }

    private function givenAttackingHunter(): void
    {
        new Hunter(
            hunterConfig: HunterConfig::fromConfigData(HunterConfigData::getByName(HunterEnum::HUNTER)),
            daedalus: $this->daedalus,
        );
    }

    private function whenIExecuteTask(): void
    {
        $this->task->execute($this->drone, $this->time);
    }

    private function thenDroneShouldMoveTo(Place $expectedRoom): void
    {
        $this->gameEquipmentService->shouldHaveReceived('moveEquipmentTo')
            ->once()
            ->withArgs(function (GameEquipment $equipment, Place $place) use ($expectedRoom) {
                return $equipment->equals($this->drone) && $place->equals($expectedRoom);
            });
    }

    private function thenDroneShouldNotMove(): void
    {
        $this->gameEquipmentService->shouldNotHaveReceived('moveEquipmentTo');
    }
}
