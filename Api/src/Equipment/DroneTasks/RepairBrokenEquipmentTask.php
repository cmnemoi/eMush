<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\DroneRepairedEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RepairBrokenEquipmentTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            return;
        }

        $equipmentToRepair = $this->getEquipmentToRepair($drone);

        if (!$equipmentToRepair) {
            // This should never happen, but throwing an exception would stall games if it ever did
            $this->taskNotApplicable = true;

            return;
        }

        // If the repair fails, increase the number of failed repair attempts and abort.
        if ($this->d100Roll->isAFailure($drone->getRepairSuccessRateForEquipment($equipmentToRepair))) {
            $this->statusService->createOrIncrementChargeStatus(
                name: EquipmentStatusEnum::DRONE_REPAIR_FAILED_ATTEMPTS,
                holder: $drone,
                time: $time,
            );

            return;
        }

        // Else, the equipment is repaired.
        $this->repairEquipment($drone, $equipmentToRepair, $time);
    }

    private function getEquipmentToRepair(Drone $drone): ?GameEquipment
    {
        $brokenRoomEquipment = $drone->getBrokenDoorsAndEquipmentsInRoom();

        return $brokenRoomEquipment->first() ?: null;
    }

    private function repairEquipment(Drone $drone, GameEquipment $equipmentToRepair, \DateTime $time): void
    {
        $this->statusService->removeStatus(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $equipmentToRepair,
            tags: [],
            time: $time,
        );

        $droneEvent = new DroneRepairedEvent(
            drone: $drone,
            repairedEquipment: $equipmentToRepair,
            time: $time,
        );
        $this->eventService->callEvent($droneEvent, DroneRepairedEvent::class);
    }
}
