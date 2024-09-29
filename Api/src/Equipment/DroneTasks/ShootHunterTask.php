<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\DroneHitHunterEvent;
use Mush\Equipment\Event\DroneKillHunterEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Status\Service\StatusServiceInterface;

class ShootHunterTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private RandomServiceInterface $randomService,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            return;
        }

        $successRate = $drone->getShootHunterSuccessRate();
        if (!$this->randomService->isSuccessful($successRate)) {
            return;
        }

        $hunter = $this->getRandomHunterFrom($drone->getDaedalus());
        $initialHealth = $hunter->getHealth();
        $damage = $this->getInflictedDamageBy($drone);

        if ($initialHealth - $damage <= 0) {
            $this->dispatchDroneKillHunterEvent($drone, $hunter, $time);
        } else {
            $this->dispatchDroneHitHunterEvent($drone, $hunter, $time);
        }
        $this->removeHealthToHunter($damage, $hunter);
    }

    private function dispatchDroneHitHunterEvent(Drone $drone, Hunter $hunter, \DateTime $time): void
    {
        $droneEvent = new DroneHitHunterEvent($drone, hunter: $hunter, time: $time);
        $this->eventService->callEvent($droneEvent, DroneHitHunterEvent::class);
    }

    private function dispatchDroneKillHunterEvent(Drone $drone, Hunter $hunter, \DateTime $time): void
    {
        $droneEvent = new DroneKillHunterEvent($drone, hunter: $hunter, time: $time);
        $this->eventService->callEvent($droneEvent, DroneKillHunterEvent::class);
    }

    private function getRandomHunterFrom(Daedalus $daedalus): Hunter
    {
        $attackingHunters = $daedalus->getAttackingHunters()->toArray();

        $hunter = $this->randomService->getRandomElement($attackingHunters);
        if (!$hunter) {
            throw new \RuntimeException('There should be at least one attacking hunter if ShootHunterTask is applicable');
        }

        return $hunter;
    }

    private function getInflictedDamageBy(Drone $drone): int
    {
        return (int) $this->randomService->getSingleRandomElementFromProbaCollection($drone->shootHunterDamageRange());
    }

    private function removeHealthToHunter(int $health, Hunter $hunter): void
    {
        $hunterVariableEvent = new HunterVariableEvent(
            hunter: $hunter,
            variableName: HunterVariableEnum::HEALTH,
            quantity: -$health,
            tags: [],
            time: new \DateTime(),
        );
        $this->eventService->callEvent($hunterVariableEvent, HunterVariableEvent::CHANGE_VARIABLE);
    }
}
