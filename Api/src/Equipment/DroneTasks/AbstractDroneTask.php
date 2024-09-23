<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Game\Service\EventServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

abstract class AbstractDroneTask
{
    protected ?self $nextTask = null;
    protected bool $taskNotApplicable = false;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService
    ) {}

    public function execute(Drone $drone, \DateTime $time): void
    {
        while ($drone->isOperational()) {
            // Apply current task effect.
            $this->applyEffect($drone, $time);

            // If current task is not applicable anymore, move to the next task.
            if ($this->taskNotApplicable) {
                $this->nextTask?->execute($drone, $time);

                return;
            }

            // Drone acts, so it consumes a charge.
            $this->removeOneDroneCharge($drone, $time);
        }
    }

    public function setNextDroneTask(self $task): void
    {
        $this->nextTask = $task;
    }

    abstract protected function applyEffect(Drone $drone, \DateTime $time): void;

    protected function removeOneDroneCharge(Drone $drone, \DateTime $time): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $drone->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            delta: -1,
            tags: [],
            time: $time,
        );
    }
}
