<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;

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
        private RepairBrokenEquipmentTask $repairBrokenEquipmentTask,
        private MoveInRandomAdjacentRoomTask $moveInRandomAdjacentRoomTask
    ) {
        $repairBrokenEquipmentTask->setNextDroneTask($moveInRandomAdjacentRoomTask);
    }

    public function execute(Drone $drone, \DateTime $time): void
    {
        // Each task will call the next one if it cannot be executed, starting with the first one.
        $this->repairBrokenEquipmentTask->execute($drone, $time);
    }
}
