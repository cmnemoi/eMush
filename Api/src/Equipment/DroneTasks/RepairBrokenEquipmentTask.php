<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\DroneRepairedEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class RepairBrokenEquipmentTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,

        // remove these once bug found
        private RoomLogServiceInterface $roomLogService,
        private TranslationServiceInterface $translationService
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            // remove this once bug found
            $this->roomLogService->createLog(
                'drone_repair_debug_nothing_broken_found',
                $drone->getPlace(),
                VisibilityEnum::PUBLIC,
                'debug',
                null,
                $this->getDroneName($drone),
                $time,
            );

            return;
        }

        // remove $time from function args once bug found
        $equipmentToRepair = $this->getEquipmentToRepair($drone, $time);

        // remove this once bug found, equipmentToRepair should never be null or false
        if (!$equipmentToRepair) {
            $this->roomLogService->createLog(
                'drone_repair_debug_fail_select',
                $drone->getPlace(),
                VisibilityEnum::PUBLIC,
                'debug',
                null,
                $this->getDroneName($drone),
                $time,
            );
            $this->taskNotApplicable = true;

            return;
        }
        $logParameters = $this->getDroneName($drone);
        $logParameters['equipment'] = $equipmentToRepair->getLogName();
        $this->roomLogService->createLog(
            'drone_repair_debug_success_select',
            $drone->getPlace(),
            VisibilityEnum::PUBLIC,
            'debug',
            null,
            $logParameters,
            $time,
        );

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

    private function getEquipmentToRepair(Drone $drone, \DateTime $time): ?GameEquipment
    {
        $brokenRoomEquipment = $drone->getBrokenDoorsAndEquipmentsInRoom();

        // remove this once bug found
        $brokenEquipmentList = '';
        foreach ($brokenRoomEquipment as $equipment) {
            $brokenEquipmentList = $brokenEquipmentList . $equipment->getLogName() . ' ';
        }

        // remove this once bug found
        $logParameters = $this->getDroneName($drone);
        $logParameters['broken_equipments'] = $brokenEquipmentList;

        // remove this once bug found
        $this->roomLogService->createLog(
            'drone_repair_debug_broken_item_list',
            $drone->getPlace(),
            VisibilityEnum::PUBLIC,
            'debug',
            null,
            $logParameters,
            $time,
        );

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

    // remove this once bug found
    private function getDroneName(Drone $drone): array
    {
        $logParameters = [];
        $logParameters['drone'] = $this->translationService->translate(
            key: 'drone',
            parameters: [
                'drone_nickname' => $drone->getNickname(),
                'drone_serial_number' => $drone->getSerialNumber(),
            ],
            domain: 'event_log',
            language: 'fr'
        );

        return $logParameters;
    }
}
