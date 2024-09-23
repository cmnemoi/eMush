<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

/**
 * This class handles drone tasks.
 *
 * It allows to execute them in the specified order, and to move to the next task if the current one is unable to be executed.
 *
 * Example : If the drone is in a room with broken equipment, it will repair it.
 * But if there is no broken equipment, it will skip directly by moving to a random adjacent room.
 */
class DroneTasksHandler
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private StatusServiceInterface $statusService,
        private ExtinguishFireTask $extinguishFireTask,
        private RepairBrokenEquipmentTask $repairBrokenEquipmentTask,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask
    ) {
        $extinguishFireTask->setNextDroneTask($repairBrokenEquipmentTask);
        $repairBrokenEquipmentTask->setNextDroneTask($moveInRandomAdjacentRoomTask);
    }

    public function execute(Drone $drone, \DateTime $time): void
    {
        $this->applyTurboUpgrade($drone, $time);

        // Each task will call the next one if it cannot be executed, starting with the first one.
        $this->extinguishFireTask->execute($drone, $time);
    }

    private function applyTurboUpgrade(Drone $drone, \DateTime $time): void
    {
        $turboWorkedChance = $drone->getChargeStatusByName(EquipmentStatusEnum::TURBO_DRONE_UPGRADE)?->getCharge() ?? 0;
        if ($this->d100Roll->isSuccessful($turboWorkedChance)) {
            $this->statusService->updateCharge(
                chargeStatus: $drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
                delta: 1,
                tags: [],
                time: $time,
            );
        }
    }
}
