<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Event\DroneMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Status\Service\StatusServiceInterface;

final class MoveInRandomAdjacentRoomTask extends AbstractDroneTask
{
    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    public function execute(Drone $drone, \DateTime $time): void
    {
        // If the drone is not operational, do not move it.
        if ($drone->isNotOperational()) {
            return;
        }

        // The drone acts, so it consumes a charge.
        $this->removeOneDroneCharge($drone, $time);

        // If there is no room to move to, execute the next task.
        $roomToMoveTo = $this->getRoomToMoveTo($drone);
        if (!$roomToMoveTo) {
            $this->nextTask?->execute($drone, $time);

            return;
        }

        // Else, move the drone to the room.
        $this->moveDroneToPlace($drone, $roomToMoveTo, $time);
    }

    private function getRoomToMoveTo(Drone $drone): ?Place
    {
        $adjacentRooms = $drone->getAdjacentRooms();
        $roomToMoveTo = $this->getRandomElementsFromArray->execute(
            elements: $adjacentRooms->toArray(),
            number: 1
        )->first();

        return $roomToMoveTo ?: null;
    }

    private function moveDroneToPlace(Drone $drone, Place $place, \DateTime $time): void
    {
        $oldRoom = $drone->getPlace();
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $drone,
            newHolder: $place,
            time: $time
        );

        $droneEvent = new DroneMovedEvent(
            drone: $drone,
            oldRoom: $oldRoom,
            time: $time
        );
        $this->eventService->callEvent($droneEvent, DroneMovedEvent::class);
    }
}
