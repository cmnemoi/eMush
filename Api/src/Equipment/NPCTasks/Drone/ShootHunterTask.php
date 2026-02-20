<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Drone;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\DroneHitHunterEvent;
use Mush\Equipment\Event\DroneKillHunterEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterVariableEnum;
use Mush\Hunter\Event\HunterVariableEvent;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class ShootHunterTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private D100RollServiceInterface $d100Roll,
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

        $patrolShip = $drone->getPilotedPatrolShip();
        $this->removeOneChargeToPatrolShip($patrolShip, $time);

        $successRate = $drone->getShootHunterSuccessRate();
        if ($this->d100Roll->isAFailure($successRate)) {
            $this->statusService->createOrIncrementChargeStatus(
                name: EquipmentStatusEnum::DRONE_SHOOT_HUNTER_FAILED_ATTEMPTS,
                holder: $drone,
                time: $time,
            );

            return;
        }

        $this->handleShootDamage($drone, $time);
    }

    private function handleShootDamage(Drone $drone, \DateTime $time): void
    {
        $hunter = $this->getRandomHunterFrom($drone->getDaedalus());
        $damage = $this->getInflictedDamageBy($drone);
        $initialHealth = $hunter->getHealth();

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
        $this->eventService->callEvent($hunterVariableEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function removeOneChargeToPatrolShip(GameEquipment $patrolShip, $time): void
    {
        $this->statusService->updateCharge(
            chargeStatus: $patrolShip->getChargeStatusByNameOrThrow(EquipmentStatusEnum::ELECTRIC_CHARGES),
            delta: -1,
            tags: [],
            time: $time,
        );
    }
}
