<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Equipment\DroneTasks;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\DroneTasksHandler;
use Mush\Equipment\DroneTasks\MoveInRandomAdjacentRoomTask;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomIntegerService as FakeGetRandomInteger;
use Mush\Game\Service\Random\GetRandomElementsFromArrayService as GetRandomElementsFromArray;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DroneTasksHandlerTest extends TestCase
{
    private StatusServiceInterface $statusService;

    private DroneTasksHandler $droneTasks;
    private RepairBrokenEquipmentTask $repairBrokenEquipmentTask;

    private Daedalus $daedalus;
    private Drone $drone;
    private GameEquipment $mycoscan;

    /**
     * @before
     */
    protected function setUp(): void
    {
        $this->statusService = \Mockery::spy(StatusServiceInterface::class);

        $this->repairBrokenEquipmentTask = new RepairBrokenEquipmentTask(
            $this->createStub(EventServiceInterface::class),
            $this->statusService,
            new FakeD100Roll(isSuccessful: false), // repair will always fail
            new GetRandomElementsFromArray(new FakeGetRandomInteger(result: 0)),
        );

        $this->droneTasks = new DroneTasksHandler(
            new FakeD100Roll(isSuccessful: true), // turbo upgrade will always succeed
            new FakeStatusService(),
            $this->repairBrokenEquipmentTask,
            $this->createStub(MoveInRandomAdjacentRoomTask::class),
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

        // then drone should try to repair twice
        $this->statusService->shouldHaveReceived('handleAttempt')->twice();
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
        $repairAction->setSuccessRate(12);

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
