<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Equipment\Event;

use Mush\Daedalus\Event\DaedalusCycleEvent;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

/**
 * @internal
 */
final class DroneChargesCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
        $this->gameEquipmentService = $I->grabService(GameEquipmentServiceInterface::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function droneBrokenAtCycleChangeShouldNotGainCharges(FunctionalTester $I): void
    {
        $drone = $this->givenADrone();
        $this->givenDroneHasCharges($drone, 1);
        $this->givenDroneWillBreakAtCycleChange($drone);

        $this->whenCycleChangeEventIsTriggered();

        $I->assertTrue($drone->isBroken(), 'Drone should be broken');
        $this->thenDroneShouldHaveCharges(1, $drone, $I);
    }

    private function givenADrone(): Drone
    {
        $drone = $this->gameEquipmentService->createGameEquipmentFromName(
            equipmentName: ItemEnum::SUPPORT_DRONE,
            equipmentHolder: $this->chun->getPlace(),
            reasons: [],
            time: new \DateTime(),
        );
        $this->setupDroneNicknameAndSerialNumber($drone, 0, 0);

        return $drone;
    }

    private function givenDroneWillBreakAtCycleChange(Drone $drone): void
    {
        /** @var ChargeStatus $slimeTrapStatus */
        $slimeTrapStatus = $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::SLIMED,
            holder: $drone,
            tags: [],
            time: new \DateTime(),
        );

        $this->statusService->updateCharge(
            chargeStatus: $slimeTrapStatus,
            delta: 1,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function givenDroneHasCharges(Drone $drone, int $charges): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            delta: $charges,
            tags: [],
            time: new \DateTime(),
            mode: VariableEventInterface::SET_VALUE,
        );
    }

    private function whenCycleChangeEventIsTriggered(): void
    {
        $event = new DaedalusCycleEvent(
            $this->daedalus,
            [EventEnum::NEW_CYCLE],
            new \DateTime()
        );
        $this->eventService->callEvent($event, DaedalusCycleEvent::DAEDALUS_NEW_CYCLE);
    }

    private function thenDroneShouldHaveCharges(int $expectedCharges, Drone $drone, FunctionalTester $I): void
    {
        $I->assertEquals(
            $expectedCharges,
            $drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge(),
            message: "Drone {$drone->getLogName()} should have {$expectedCharges} charges, but has {$drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES)->getCharge()}"
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
