<?php

declare(strict_types=1);

namespace Mush\Equipment\NPCTasks\Drone;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\SpaceShip;
use Mush\Equipment\Event\DroneTakeoffEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

class TakeoffTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private GetRandomElementsFromArrayServiceInterface $getRandomArrayElementsFrom,
        private GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        if ($drone->cannotApplyTask($this)) {
            $this->taskNotApplicable = true;

            return;
        }

        $this->handleTakeoff($drone, $this->selectedPatrolShip($drone), $time);
    }

    private function handleTakeoff(Drone $drone, SpaceShip $patrolShip, \DateTime $time): void
    {
        $this->dispatchDroneTakeoffEvent($drone, $time);
        $this->movePatrolShipToItsPlace($patrolShip, $time);
        $this->moveDroneToPatrolShipPlace($drone, $patrolShip, $time);
    }

    private function movePatrolShipToItsPlace(SpaceShip $patrolShip, \DateTime $time): void
    {
        $patrolShipPlace = $patrolShip->getDaedalus()->getPlaceByNameOrThrow($patrolShip->getPatrolShipName());

        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $patrolShip,
            newHolder: $patrolShipPlace,
            time: $time,
        );
    }

    private function moveDroneToPatrolShipPlace(Drone $drone, SpaceShip $patrolShip, \DateTime $time): void
    {
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $drone,
            newHolder: $this->patrolShipPlace($patrolShip),
            time: $time,
        );
    }

    private function dispatchDroneTakeoffEvent(Drone $drone, \DateTime $time): void
    {
        $this->eventService->callEvent(
            event: new DroneTakeoffEvent(drone: $drone, time: $time),
            name: DroneTakeoffEvent::class,
        );
    }

    private function selectedPatrolShip(Drone $drone): SpaceShip
    {
        $patrolShip = $this->getRandomArrayElementsFrom->execute($drone->operationalPatrolShipsInRoom(), number: 1)->first();

        return $patrolShip instanceof SpaceShip ? $patrolShip : throw new \RuntimeException('Patrol ship should be a SpaceShip');
    }

    private function patrolShipPlace(SpaceShip $patrolShip): Place
    {
        return $patrolShip->getDaedalus()->getPlaceByNameOrThrow($patrolShip->getPatrolShipName());
    }
}
