<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\AiHandler;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\AIHandlerEnum;
use Mush\Equipment\NPCTasks\Drone\ExtinguishFireTask;
use Mush\Equipment\NPCTasks\Drone\LandTask;
use Mush\Equipment\NPCTasks\Drone\MoveTask;
use Mush\Equipment\NPCTasks\Drone\RepairBrokenEquipmentTask;
use Mush\Equipment\NPCTasks\Drone\ShootHunterTask;
use Mush\Equipment\NPCTasks\Drone\TakeoffTask;
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
class DroneTasksHandler extends AbstractAiHandler
{
    protected string $name = AIHandlerEnum::DRONE->value;

    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private StatusServiceInterface $statusService,
        private ExtinguishFireTask $extinguishFireTask,
        private RepairBrokenEquipmentTask $repairBrokenEquipmentTask,
        private TakeoffTask $takeoffTask,
        private ShootHunterTask $shootHunterTask,
        private LandTask $landTask,
        private MoveTask $moveTask
    ) {
        $extinguishFireTask->setNextDroneTask($repairBrokenEquipmentTask);
        $repairBrokenEquipmentTask->setNextDroneTask($takeoffTask);
        $takeoffTask->setNextDroneTask($shootHunterTask);
        $shootHunterTask->setNextDroneTask($landTask);
        $landTask->setNextDroneTask($moveTask);
    }

    public function execute(GameEquipment $NPC, \DateTime $time): void
    {
        if (!$NPC instanceof Drone) {
            throw new \RuntimeException("Equipment {$NPC->getName()} should be a drone");
        }

        $this->applyTurboUpgrade($NPC, $time);

        // Each task will call the next one if it cannot be executed, starting with the first one.
        while ($NPC->isOperational()) {
            $this->extinguishFireTask->execute($NPC, $time);
        }
    }

    private function applyTurboUpgrade(Drone $drone, \DateTime $time): void
    {
        if ($drone->isTurbo() && $this->d100Roll->isSuccessful($drone->turboChance())) {
            $this->statusService->createOrIncrementChargeStatus(
                name: EquipmentStatusEnum::ELECTRIC_CHARGES,
                holder: $drone,
                time: $time,
            );
        }
    }
}
