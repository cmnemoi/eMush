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
            [$currentRoom, $destinationRoom] = $roomsToVisit->dequeue();

            if ($condition($currentRoom)) {
                // returns the adjacent room that's on the path to the condition, or null if the starting room fullfils it
                return $destinationRoom;
            }

            $this->addUnvisitedRoomsToQueue($currentRoom, $destinationRoom, $roomsToVisit, $visitedRooms);
        }

        return null;
    }

    private function addUnvisitedRoomsToQueue(
        Place $currentRoom,
        ?Place $destinationRoom,
        \SplQueue $queue,
        array &$visitedRooms
    ): void {
        foreach ($currentRoom->getAccessibleRooms() as $adjacentRoom) {
            // If the room has already been visited, skip it
            if (isset($visitedRooms[$adjacentRoom->getId()])) {
                continue;
            }

            // if this is the first depth (ie destinationRoom is null), destinationRoom is set to adjacentRoom
            $nextRoom = $destinationRoom ?? $adjacentRoom;

            // Add the room to the queue
            $queue->enqueue([$adjacentRoom, $nextRoom]);

            // Mark room as visited
            $visitedRooms[$adjacentRoom->getId()] = true;
        }
    }
}
