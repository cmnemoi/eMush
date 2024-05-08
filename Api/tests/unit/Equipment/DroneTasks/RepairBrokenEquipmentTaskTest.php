<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\DroneTasks;

use Codeception\PHPUnit\TestCase;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Service\EventService;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\Random\FakeGetRandomElementsFromArrayService;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;

/**
 * @internal
 */
final class RepairBrokenEquipmentTaskTest extends TestCase
{
    private RepairBrokenEquipmentTask $task;

    /**
     * @before
     */
    protected function setUp(): void
    {
        /** @var EventService $stubEventService */
        $stubEventService = $this->createStub(EventService::class);

        $this->task = new RepairBrokenEquipmentTask(
            $stubEventService,
            new FakeStatusService(),
            new FakeD100Roll(isAFailure: true),
            new FakeGetRandomElementsFromArrayService(),
        );
    }

    public function testShouldIncreaseDroneSuccessRateAfterAFailure(): void
    {
        // Given a broken mycoscan in the room
        $daedalus = DaedalusFactory::createDaedalus();
        $lab = $daedalus->getPlaceByName(RoomEnum::LABORATORY);

        $mycoscan = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::MYCOSCAN,
            $lab,
        );
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::BROKEN,
            $mycoscan,
        );
        $repairAction = new Action();
        $repairAction->setActionName(ActionEnum::REPAIR);
        $mycoscan->getEquipment()->setActions([$repairAction]);

        // Given a charged support drone in the room
        $drone = GameEquipmentFactory::createDroneForHolder($lab);
        $chargesStatus = $drone->getChargesStatus()->setCharge(1);

        // given the support drone has a 12% success rate to repair the mycoscan
        $repairAction->setSuccessRate(12);

        // When I execute the repair task
        $this->task->execute($drone, new \DateTime());

        // Then drone repair success rate should increase by 3 points
        self::assertSame(15, $drone->getRepairSuccessRateForEquipment($mycoscan));

        // Then drone charges should decrease by 1
        self::assertSame(0, $chargesStatus->getCharge());
    }
}
