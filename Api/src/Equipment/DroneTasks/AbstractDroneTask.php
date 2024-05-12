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

    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService
    ) {}

    abstract public function execute(Drone $drone, \DateTime $time): void;

    public function setNextDroneTask(self $task): void
    {
        $this->nextTask = $task;
    }

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
