<?php

declare(strict_types=1);

namespace Mush\Equipment\DroneTasks;

use Mush\Equipment\Entity\Drone;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\DroneMovedEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\GetRandomElementsFromArrayServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Service\StatusServiceInterface;

class MoveTask extends AbstractDroneTask
{
    private const PRIORITY_FIRE = 4;
    private const PRIORITY_BROKEN_EQUIPMENT = 3;
    private const PRIORITY_PATROL_SHIP = 2;
    private const PRIORITY_RANDOM_ROOM = 1;

    public function __construct(
        protected EventServiceInterface $eventService,
        protected StatusServiceInterface $statusService,
        private FindNextRoomTowardsConditionService $findNextRoomTowardsCondition,
        private GameEquipmentServiceInterface $gameEquipmentService,
        private GetRandomElementsFromArrayServiceInterface $getRandomElementsFromArray,
    ) {
        parent::__construct($this->eventService, $this->statusService);
    }

    protected function applyEffect(Drone $drone, \DateTime $time): void
    {
        $destinationRoom = $this->findDestinationRoom($drone);
        if (!$destinationRoom) {
            $this->taskNotApplicable = true;

            return;
        }

        $this->moveDroneToPlace($drone, $destinationRoom, $time);
    }

    private function findDestinationRoom(Drone $drone): ?Place
    {
        return $drone->isSensor() ? $this->findRoomUsingSensorStrategies($drone) : $this->findRandomAdjacentRoom($drone);
    }

    private function moveDroneToPlace(Drone $drone, Place $place, \DateTime $time): void
    {
        $oldRoom = $drone->getPlace();
        $this->gameEquipmentService->moveEquipmentTo(
            equipment: $drone,
            newHolder: $place,
            time: $time
        );

        $this->dispatchDroneMovedEvent($drone, $oldRoom, $time);
    }

    private function dispatchDroneMovedEvent(Drone $drone, Place $oldRoom, \DateTime $time): void
    {
        $droneEvent = new DroneMovedEvent(
            drone: $drone,
            oldRoom: $oldRoom,
            time: $time
        );
        $this->eventService->callEvent($droneEvent, DroneMovedEvent::class);
    }

    private function findRoomUsingSensorStrategies(Drone $drone): ?Place
    {
        $strategies = $this->getSensorStrategies($drone);

        foreach ($strategies as $findRoomFor) {
            $room = $findRoomFor($drone);
            if ($room) {
                return $room;
            }
        }

        return null;
    }

    /**
     * @return array<\Closure(Drone): ?Place>
     */
    private function getSensorStrategies(Drone $drone): array
    {
        $strategies = [];

        if ($drone->isFirefighter()) {
            $strategies[self::PRIORITY_FIRE] = fn (Drone $drone) => $this->findNextRoomToFire($drone);
        }
        if ($drone->isPilot() && $drone->huntersAreAttacking()) {
            $strategies[self::PRIORITY_PATROL_SHIP] = fn (Drone $drone) => $this->findNextRoomToOperationalPatrolShip($drone);
        }

        $strategies[self::PRIORITY_BROKEN_EQUIPMENT] = fn (Drone $drone) => $this->findNextRoomToBrokenEquipment($drone);
        $strategies[self::PRIORITY_RANDOM_ROOM] = fn (Drone $drone) => $this->findRandomAdjacentRoom($drone);

        // Sort strategies by priority (highest first)
        krsort($strategies);

        return array_values($strategies);
    }

    private function findNextRoomToFire(Drone $drone): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($drone->getPlace(), static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE));
    }

    private function findNextRoomToBrokenEquipment(Drone $drone): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($drone->getPlace(), static fn (Place $room) => $room->getBrokenDoorsAndEquipments()->count() > 0);
    }

    private function findNextRoomToOperationalPatrolShip(Drone $drone): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($drone->getPlace(), static fn (Place $room) => $room->getEquipments()->filter(
            static fn (GameEquipment $gameEquipment) => $gameEquipment->isAPatrolShip() && $gameEquipment->isOperational()
        )->count() > 0);
    }

    private function findRandomAdjacentRoom(Drone $drone): ?Place
    {
        return $this->getRandomElementsFromArray->execute(
            elements: $drone->getAdjacentRooms()->toArray(),
            number: 1
        )->first() ?: null;
    }
}
