<?php

declare(strict_types=1);

namespace Mush\tests\unit\Equipment\DroneTasks;

use Codeception\PHPUnit\TestCase;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Factory\DaedalusFactory;
use Mush\Equipment\DroneTasks\RepairBrokenEquipmentTask;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Factory\GameEquipmentFactory;
use Mush\Game\Service\EventService;
use Mush\Game\Service\Random\FakeD100RollService as FakeD100Roll;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Factory\StatusFactory;
use Mush\Status\Service\FakeStatusService;

/**
 * @internal
 */
final class RepairBrokenEquipmentTaskTest extends TestCase
{
    private RepairBrokenEquipmentTask $task;

    private Daedalus $daedalus;
    private Drone $drone;
    private GameEquipment $mycoscan;

    /**
     * @before
     */
    protected function setUp(): void
    {
        /** @var EventService $stubEventService */
        $stubEventService = self::createStub(EventService::class);

        /** @var RoomLogServiceInterface $roomLogService */
        $roomLogService = self::createStub(RoomLogServiceInterface::class);

        /** @var TranslationServiceInterface $translationService */
        $translationService = self::createStub(TranslationServiceInterface::class);

        $this->task = new RepairBrokenEquipmentTask(
            $stubEventService,
            new FakeStatusService(),
            new FakeD100Roll(isSuccessful: false),
            $roomLogService,
            $translationService,
        );

        $this->daedalus = DaedalusFactory::createDaedalus();
    }

    public function testShouldIncreaseDroneSuccessRateAfterAFailure(): void
    {
        $this->givenBrokenMycoscanInTheRoom();
        $this->givenSupportDroneWithOneChargeInTheRoom();
        $this->givenSupportDroneHasTwelveSuccessRateToRepairTheMycoscan();

        $this->whenIRepairTheBrokenMycoscan();

        $this->thenDroneRepairSuccessRateShouldIncreaseByThreePoints();
    }

    public function testShouldDecreaseDroneChargeAfterAFailure(): void
    {
        $this->givenBrokenMycoscanInTheRoom();
        $this->givenSupportDroneWithOneChargeInTheRoom();
        $this->givenSupportDroneHasTwelveSuccessRateToRepairTheMycoscan();

        $this->whenIRepairTheBrokenMycoscan();

        $this->thenDroneChargeShouldDecreaseByOne();
    }

    private function givenBrokenMycoscanInTheRoom(): void
    {
        $lab = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        $this->mycoscan = GameEquipmentFactory::createEquipmentByNameForHolder(
            EquipmentEnum::MYCOSCAN,
            $lab,
        );
        StatusFactory::createStatusByNameForHolder(
            EquipmentStatusEnum::BROKEN,
            $this->mycoscan,
        );
    }

    private function givenSupportDroneWithOneChargeInTheRoom(): void
    {
        $lab = $this->daedalus->getPlaceByNameOrThrow(RoomEnum::LABORATORY);

        $this->drone = GameEquipmentFactory::createDroneForHolder($lab);
        $this->drone->getChargeStatus()->setCharge(1);
    }

    private function givenSupportDroneHasTwelveSuccessRateToRepairTheMycoscan(): void
    {
        $repairAction = new ActionConfig();
        $repairAction->setActionName(ActionEnum::REPAIR);
        $repairAction->setSuccessRate(12);

        $this->mycoscan->getEquipment()->setActionConfigs([$repairAction]);
    }

    private function whenIRepairTheBrokenMycoscan(): void
    {
        $this->task->execute($this->drone, new \DateTime());
    }

    private function thenDroneRepairSuccessRateShouldIncreaseByThreePoints(): void
    {
        self::assertSame(15, $this->drone->getRepairSuccessRateForEquipment($this->mycoscan));
    }

    private function thenDroneChargeShouldDecreaseByOne(): void
    {
        self::assertSame(0, $this->drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge());
    }
}
