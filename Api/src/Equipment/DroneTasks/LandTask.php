<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\DroneLandedEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Service\StatusServiceInterface;

class LandTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            return;
        }

        $this->handleLanding($drone, $time);
        $this->dispatchDroneLandedEvent($drone, $time);
    }

    private function handleLanding(Drone $drone, \DateTime $time): void
    {
        $this->patrolShipManoeuvreService->handleLand(
            patrolShip: $drone->getPilotedPatrolShip(),
            pilot: Player::createNull(),
            actionResult: new CriticalSuccess(),
            time: $time,
        );
    }

    private function dispatchDroneLandedEvent(Drone $drone, \DateTime $time): void
    {
        $this->eventService->callEvent(
            event: new DroneLandedEvent(drone: $drone, time: $time),
            name: DroneLandedEvent::class,
        );
    }
}
