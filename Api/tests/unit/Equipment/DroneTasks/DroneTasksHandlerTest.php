<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\DroneTasks;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Repository\InMemoryActionConfigRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\DroneTasks\ExtinguishFireTask;
use Mush\Equipment\DroneTasks\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as D100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as GetRandomInteger;
use Mush\Game\Service\Random\GetRandomElementsFromArrayService as GetRandomElementsFromArray;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
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

    private Daedalus $daedalus;
    private Drone $drone;
    private GameEquipment $mycoscan;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->extinguishFireTask = new ExtinguishFireTask(
            $this->createStub(EventServiceInterface::class),
            new StatusService(),
            new InMemoryActionConfigRepository(),
            new D100Roll(isSuccessful: false), // extinguish fire will always fail
        );

        $this->repairBrokenEquipmentTask = new RepairBrokenEquipmentTask(
            $this->createStub(EventServiceInterface::class),
            new StatusService(),
            new D100Roll(isSuccessful: false), // repair will always fail
            new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );

        $this->moveInRandomAdjacentRoomTask = new MoveInRandomAdjacentRoomTask(
            $this->createStub(EventServiceInterface::class),
            new StatusService(),
            $this->createStub(GameEquipmentServiceInterface::class),
            new GetRandomElementsFromArray(new GetRandomInteger(result: 0)),
        );

        $this->droneTasks = new DroneTasksHandler(
            d100Roll: new D100Roll(isSuccessful: true), // turbo upgrade will always succeed
            statusService: new StatusService(),
            extinguishFireTask: $this->extinguishFireTask,
            repairBrokenEquipmentTask: $this->repairBrokenEquipmentTask,
            moveInRandomAdjacentRoomTask: $this->moveInRandomAdjacentRoomTask,
        );

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
    }

    public function testTurboUpgradeAllowsDroneToActTwice(): void
    {
        // given drone has turbo upgrade
        $this->givenDroneHasTurboUpgrade();

        // when drone acts
        $this->droneTasks->execute($this->drone, new \DateTime());

        // then drone should fail to repair twice so its success rate should be 93%
        self::assertEquals(93, $this->drone->getRepairSuccessRateForEquipment($this->mycoscan));
    }

    private function givenDroneInRoom(): void
    {
        $this->drone = GameEquipmentFactory::createDroneForHolder($this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY));
        $this->drone->getChargeStatus()->setCharge(1);
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
}