<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Drone;

use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\DroneLandedEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Service\StatusServiceInterface;

class LandTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService,
        private PlayerServiceInterface $playerService,
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
        $patrolShip = $drone->getPilotedPatrolShip();
        $dockingPlace = $patrolShip->getDaedalus()->getPlaceByNameOrThrow($patrolShip->getDockingPlace());

        foreach ($drone->getPlace()->getAlivePlayers() as $player) {
            $this->playerService->changePlace($player, $dockingPlace);
        }

        $this->patrolShipManoeuvreService->handleLand(
            patrolShip: $patrolShip,
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
