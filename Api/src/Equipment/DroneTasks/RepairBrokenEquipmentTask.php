<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Action\Entity\ActionResult\Fail;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\DroneTaskEnum;
use Mush\Equipment\Event\DroneRepairedEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class RepairBrokenEquipmentTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    public function execute(Drone $drone, \DateTime $time): void
    {
        // If the drone is not operational, do not repair equipment.
        if ($drone->isNotOperational()) {
            return;
        }

        $equipmentToRepair = $this->getEquipmentToRepair($drone);
        // If there is no broken equipment in the room, execute the next task.
        if (!$equipmentToRepair) {
            $this->nextTask?->execute($drone, $time);

            return;
        }

        // The drone acts, so it consumes a charge.
        $this->removeOneDroneCharge($drone, $time);

        // If the repair fails, increase the number of failed repair attempts.
        if ($this->d100Roll->isAFailure($drone->getRepairSuccessRateForEquipment($equipmentToRepair))) {
            $this->statusService->handleAttempt(
                holder: $drone,
                actionName: DroneTaskEnum::REPAIR_BROKEN_EQUIPMENT->value,
                result: new Fail(),
                tags: [],
                time: $time,
            );

            return;
        }

        // Else, the equipment is repaired.
        $this->repairEquipment($drone, $equipmentToRepair, $time);
    }

    private function getEquipmentToRepair(Drone $drone): ?GameEquipment
    {
        $brokenRoomEquipment = $drone->getBrokenEquipmentsInRoom();
        $equipmentToRepair = $this->getRandomElementsFromArray->execute(
            elements: $brokenRoomEquipment->toArray(),
            number: 1
        )->first();

        return $equipmentToRepair ?: null;
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
