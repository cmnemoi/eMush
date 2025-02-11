<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\DroneTasks;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\InMemoryActionConfigRepository;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\DroneTasks\ExtinguishFireTask;
use Mush\Equipment\DroneTasks\LandTask;
use Mush\Equipment\DroneTasks\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\DroneTasks\ShootHunterTask;
use Mush\Equipment\DroneTasks\TakeoffTask;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as GetRandomInteger;
use Mush\Game\Service\Random\GetRandomElementsFromArrayService as GetRandomElementsFromArray;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService as StatusService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DroneTasksHandlerTest extends TestCase
{
    private DroneTasksHandler $droneTasks;
    private ExtinguishFireTask $extinguishFireTask;
    private RepairBrokenEquipmentTask $repairBrokenEquipmentTask;
    private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask;
    private TakeoffTask $takeoffTask;
    private ShootHunterTask $shootHunterTask;

    private LandTask $landTask;

    private StatusService $statusService;

    private Daedalus $daedalus;
    private Drone $drone;
    private GameEquipment $mycoscan;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->statusService = new StatusService();

        $this->extinguishFireTask = $this->createExtinguishFireTask(false);
        $this->repairBrokenEquipmentTask = $this->createRepairBrokenEquipmentTask(false);
        $this->moveInRandomAdjacentRoomTask = $this->createMoveInRandomAdjacentRoomTask();
        $this->takeoffTask = $this->createTakeoffTask();
        $this->shootHunterTask = $this->createShootHunterTask(false);
        $this->landTask = $this->createLandTask();

        $this->droneTasks = $this->createDroneTasksHandler();

        $this->daedalus = DaedalusFactory::createDaedalus();
        $this->givenDroneInRoom();
        $this->givenBrokenMycoscanInTheRoom();
    }

    /**
     * @after
     */
    protected function tearDown(): void
    {
        \Mockery::close();

        $this->statusService->statuses->clear();
    }

    public function testTurboShouldNotApplyIfDroneDoesNotHaveRelevantUpgrade(): void
    {
        $this->droneTasks->execute($this->drone, new \DateTime());
        self::assertEquals(75, $this->drone->getRepairSuccessRateForEquipment($this->mycoscan));
    }

    public function testTurboUpgradeAllowsDroneToActTwice(): void
    {
        $this->givenDroneHasTurboUpgrade();
        $this->droneTasks->execute($this->drone, new \DateTime());
        self::assertEquals(93, $this->drone->getRepairSuccessRateForEquipment($this->mycoscan));
    }

    public function testMultipleTasksSuccessful(): void
    {
        $this->givenDroneHasTurboUpgrade();
        $this->givenDroneIsFirefighter();
        $this->givenAFireInTheRoom();
        $this->givenAnAdjacentRoom();

        $successfulExtinguishFireTask = $this->createExtinguishFireTask(true);
        $successfulRepairTask = $this->createRepairBrokenEquipmentTask(true);
        $moveEquipmentService = \Mockery::spy(GameEquipmentServiceInterface::class);
        $moveInRandomAdjacentRoomTask = $this->createMoveInRandomAdjacentRoomTask($moveEquipmentService);

        $droneTasks = $this->createDroneTasksHandler($successfulExtinguishFireTask, $successfulRepairTask, $moveInRandomAdjacentRoomTask);
        $droneTasks->execute($this->drone, new \DateTime());

        self::assertFalse($this->drone->getPlace()->hasStatus(StatusEnum::FIRE));
        self::assertFalse($this->mycoscan->isBroken());
        $moveEquipmentService->shouldNotHaveReceived('moveEquipmentTo');
    }

    public function testOneTaskUnavailableTheTwoOthersSuccessful(): void
    {
        $this->givenDroneHasTurboUpgrade();
        $this->givenAFireInTheRoom();
        $this->givenAnAdjacentRoom();

        $successfulRepairTask = $this->createRepairBrokenEquipmentTask(true);
        $moveEquipmentService = \Mockery::spy(GameEquipmentServiceInterface::class);
        $moveInRandomAdjacentRoomTask = $this->createMoveInRandomAdjacentRoomTask($moveEquipmentService);

        $droneTasks = $this->createDroneTasksHandler($this->extinguishFireTask, $successfulRepairTask, $moveInRandomAdjacentRoomTask);
        $droneTasks->execute($this->drone, new \DateTime());

        self::assertTrue($this->drone->getPlace()->hasStatus(StatusEnum::FIRE));
        self::assertFalse($this->mycoscan->isBroken());
        $moveEquipmentService->shouldHaveReceived('moveEquipmentTo')->once();
    }

    private function givenDroneInRoom(): void
    {
        $this->drone = GameEquipmentFactory::createDroneForHolder($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $chargeStatus = $this->drone->getChargeStatus();
        $chargeStatus->setCharge(1);
        $this->statusService->persist($chargeStatus);
    }

    private function givenBrokenMycoscanInTheRoom(): void
    {
        $lab = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        $this->mycoscan = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::MYCOSCAN,
            $lab,
        );

        $repairAction = new ActionConfig();
        $repairAction->setActionName(ActionEnum::REPAIR);
        $repairAction->setSuccessRate(60);

        $this->mycoscan->getEquipment()->setActionConfigs([$repairAction]);

        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::BROKEN,
            $this->mycoscan,
        );
    }

    private function givenDroneHasTurboUpgrade(): void
    {
        StatusFactory::createChargeStatusFromStatusName(
            EquipmentStatusEnum::TURBO_DRONE_UPGRADE,
            $this->drone,
        );
    }

    private function givenDroneIsFirefighter(): void
    {
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::FIREFIGHTER_DRONE_UPGRADE,
            $this->drone,
        );
    }

    private function givenAFireInTheRoom(): void
    {
        $fireStatus = StatusFactory::createStatusByNameForHolder(
            StatusEnum::FIRE,
            $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY),
        );
        $this->statusService->persist($fireStatus);
    }

    private function givenAnAdjacentRoom(): void
    {
        $adjacentRoom = Place::createRoomByNameInDaedalus(RoomEnum::LABORATORY, $this->daedalus);
        Door::createFromRooms($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY), $adjacentRoom);
    }

    private function createDroneTasksHandler(
        ?ExtinguishFireTask $extinguishFireTask = null,
        ?RepairBrokenEquipmentTask $repairBrokenEquipmentTask = null,
        ?MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask = null
    ): DroneTasksHandler {
        return new DroneTasksHandler(
            d100Roll: new D100Roll(isSuccessful: true),
            statusService: $this->statusService,
            extinguishFireTask: $extinguishFireTask ?? $this->extinguishFireTask,
            repairBrokenEquipmentTask: $repairBrokenEquipmentTask ?? $this->repairBrokenEquipmentTask,
            takeoffTask: $this->takeoffTask,
            shootHunterTask: $this->shootHunterTask,
            landTask: $this->landTask,
            moveInRandomAdjacentRoomTask: $moveInRandomAdjacentRoomTask ?? $this->moveInRandomAdjacentRoomTask,
        );
    }

    private function createExtinguishFireTask(bool $isSuccessful): ExtinguishFireTask
    {
        return new ExtinguishFireTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            new InMemoryActionConfigRepository(),
            new D100Roll(isSuccessful: $isSuccessful),
        );
    }

    private function createRepairBrokenEquipmentTask(bool $isSuccessful): RepairBrokenEquipmentTask
    {
        return new RepairBrokenEquipmentTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            new D100Roll(isSuccessful: $isSuccessful),
            $this->createStub(RoomLogServiceInterface::class),
            $this->createStub(TranslationServiceInterface::class),
        );
    }

    private function createMoveInRandomAdjacentRoomTask($equipmentService = null): MoveInRandomAdjacentRoomTask
    {
        return new MoveInRandomAdjacentRoomTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            $equipmentService ?: $this->createStub(GameEquipmentServiceInterface::class),
            new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );
    }

    private function createTakeoffTask(): TakeoffTask
    {
        return new TakeoffTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
            $this->createStub(GameEquipmentServiceInterface::class),
        );
    }

    private function createShootHunterTask(bool $isSuccessful): ShootHunterTask
    {
        return new ShootHunterTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            new D100Roll(isSuccessful: $isSuccessful),
            $this->createStub(RandomServiceInterface::class),
        );
    }

    private function createLandTask(): LandTask
    {
        return new LandTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            $this->createStub(PatrolShipManoeuvreServiceInterface::class),
            $this->createStub(PlayerServiceInterface::class),
        );
    }
}
