<?php

declare(strict_types=1);

namespace Mush\Place\Service;

use Mush\Place\Entity\Place;

final class FindNextRoomTowardsConditionService
{
    /**
     * Find the next room that satisfies the given condition.
     * Note: If the start room satisfies the condition, this returns `null`.
     *
     * @param \Closure(Place): bool $condition - A closure on a Place object which returns true if the room satisfies the condition
     */
    public function execute(Place $startRoom, \Closure $condition): ?Place
    {
        $roomsToVisit = new \SplQueue();
        $roomsToVisit->enqueue([$startRoom, null]);
        $visitedRooms = [$startRoom->getId() => true];

        while (!$roomsToVisit->isEmpty()) {
            [$currentRoom, $previousRoom] = $roomsToVisit->dequeue();

            if ($previousRoom && $condition($currentRoom)) {
                return $previousRoom;
            }

            $this->addUnvisitedRoomsToQueue($currentRoom, $previousRoom, $roomsToVisit, $visitedRooms);
        }

        return null;
    }

    private function addUnvisitedRoomsToQueue(
        Place $currentRoom,
        ?Place $previousRoom,
        \SplQueue $queue,
        array &$visitedRooms
    ): void {
        foreach ($currentRoom->getAdjacentRooms() as $adjacentRoom) {
            // If the room has already been visited, skip it
            if (isset($visitedRooms[$adjacentRoom->getId()])) {
                continue;
            }

            // Add the room to the queue
            $nextRoom = $previousRoom ?? $adjacentRoom;
            $queue->enqueue([$adjacentRoom, $nextRoom]);

            // Mark room as visited
            $visitedRooms[$adjacentRoom->getId()] = true;
        }
    }
}
