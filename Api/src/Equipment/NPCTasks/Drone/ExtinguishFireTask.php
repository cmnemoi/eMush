<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Drone;

use Mush\Action\Repository\ActionConfigRepositoryInterface;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\DroneExtinguishedFireEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ExtinguishFireTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private ActionConfigRepositoryInterface $actionConfigRepository,
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

        // If the drone fails to extinguish the fire, do not proceed.
        if ($this->d100Roll->isAFailure($drone->getExtinguishFireSuccessRate($this->actionConfigRepository))) {
            $this->statusService->createOrIncrementChargeStatus(
                name: EquipmentStatusEnum::DRONE_EXTINGUISH_FAILED_ATTEMPTS,
                holder: $drone,
                time: $time,
            );

            return;
        }

        // Else, extinguish the fire.
        $this->extinguishFire($drone, $time);
    }

    private function extinguishFire(Drone $drone, \DateTime $time): void
    {
        $this->statusService->removeStatus(
            statusName: StatusEnum::FIRE,
            holder: $drone->getPlace(),
            tags: [],
            time: $time,
        );

        $this->eventService->callEvent(
            event: new DroneExtinguishedFireEvent(drone: $drone, time: $time),
            name: DroneExtinguishedFireEvent::class,
        );
    }
}
