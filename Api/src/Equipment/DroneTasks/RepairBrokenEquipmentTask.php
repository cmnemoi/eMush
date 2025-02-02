<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\DroneRepairedEvent;
use Mush\Game\Exception\GameException;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RepairBrokenEquipmentTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            return;
        }

        $equipmentToRepair = $this->getEquipmentToRepairOrThrow($drone);

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

    private function getEquipmentToRepairOrThrow(Drone $drone): GameEquipment
    {
        $brokenRoomEquipment = $drone->getBrokenDoorsAndEquipmentsInRoom();
        $equipmentToRepair = $this->getRandomElementsFromArray->execute(
            elements: $brokenRoomEquipment->toArray(),
            number: 1
        )->first();

        if (!$equipmentToRepair) {
            $brokenEquipmentList = '';
            foreach ($brokenRoomEquipment as $equipment) {
                $brokenEquipmentList = $brokenEquipmentList . $equipment->getName() . ' ';
            }

            throw new GameException($drone->getNickname() . ' failed to select an equipment to repair, even though the following broken equipments have been detected in ' . $drone->getPlace()->getName() . ': ' . $brokenEquipmentList . '. If the code got this far, the error is in GetRandomElementsFromArrayService. If this throw isnt triggering yet drones are still reported leaving rooms with broken items, then getBrokenDoorsAndEquipmentsInRoom is to blame.');
        }

        return $equipmentToRepair;
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
