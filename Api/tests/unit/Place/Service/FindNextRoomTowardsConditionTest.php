<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Place\Service;

use Mush\Equipment\Entity\Door;
use Mush\Place\Entity\Place;
use Mush\Place\Service\FindNextRoomTowardsConditionService;
use Mush\Status\Enum\StatusEnum;
use Mush\Status\Factory\StatusFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class FindNextRoomTowardsConditionTest extends TestCase
{
    private FindNextRoomTowardsConditionService $findNextRoomTowardsCondition;
    private Place $startRoom;
    private Place $adjacentRoom1;
    private Place $adjacentRoom2;
    private Place $targetRoom;
    private Place $distantRoom;

    protected function setUp(): void
    {
        $this->findNextRoomTowardsCondition = new FindNextRoomTowardsConditionService();
    }

    public function testShouldFindPathToClosestRoomMatchingCondition(): void
    {
        // Given
        $this->givenARoomLayoutWithFourConnectedRooms();
        $this->givenTargetRoomHasFire();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertTrue($nextRoom->equals($this->adjacentRoom1));
    }

    public function testShouldReturnNullWhenNoRoomMatchesCondition(): void
    {
        // Given
        $this->givenARoomLayoutWithTwoConnectedRooms();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertNull($nextRoom);
    }

    public function testShouldHandleCyclicConnections(): void
    {
        // Given
        $this->givenARoomLayoutWithThreeRoomsInCycle();
        $this->givenLastRoomHasFire();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertTrue($nextRoom->equals($this->targetRoom));
    }

    public function testShouldReturnNullWhenStartRoomMatchesCondition(): void
    {
        // Given
        $this->givenARoomLayoutWithTwoConnectedRooms();
        $this->givenStartRoomHasFire();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertNull($nextRoom);
    }

    public function testShouldReturnFirstAdjacentRoomWhenMultipleTargetsExistAtSameDistance(): void
    {
        // Given
        $this->givenARoomLayoutWithMultipleTargetsAtSameDistance();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertTrue($nextRoom->equals($this->adjacentRoom1), 'Expected room to be adjacentRoom1');
    }

    public function testShouldFindShortestPathInComplexGraph(): void
    {
        // Given
        $this->givenAComplexRoomLayout();
        $this->givenDistantRoomHasFire();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertTrue($nextRoom->equals($this->adjacentRoom2));
    }

    public function testShouldReturnNullForUnreachableRoom(): void
    {
        // Given
        $this->givenARoomLayoutWithIsolatedTarget();
        $this->givenTargetRoomHasFire();

        // When
        $nextRoom = $this->whenFindingClosestRoomWithCondition(
            static fn (Place $room) => $room->hasStatus(StatusEnum::FIRE)
        );

        // Then
        self::assertNull($nextRoom);
    }

    private function givenARoomLayoutWithFourConnectedRooms(): void
    {
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        $this->adjacentRoom2 = Place::createRoomByName('room3');
        $this->setPlaceId($this->adjacentRoom2, 3);

        $this->targetRoom = Place::createRoomByName('room4');
        $this->setPlaceId($this->targetRoom, 4);

        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
        Door::createFromRooms($this->startRoom, $this->adjacentRoom2);
        Door::createFromRooms($this->adjacentRoom1, $this->targetRoom);
    }

    private function givenARoomLayoutWithTwoConnectedRooms(): void
    {
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
    }

    private function givenARoomLayoutWithThreeRoomsInCycle(): void
    {
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        $this->targetRoom = Place::createRoomByName('room3');
        $this->setPlaceId($this->targetRoom, 3);

        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
        Door::createFromRooms($this->adjacentRoom1, $this->targetRoom);
        Door::createFromRooms($this->targetRoom, $this->startRoom);
    }

    private function givenARoomLayoutWithMultipleTargetsAtSameDistance(): void
    {
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        $this->adjacentRoom2 = Place::createRoomByName('room3');
        $this->setPlaceId($this->adjacentRoom2, 3);

        $this->targetRoom = Place::createRoomByName('room4');
        $this->setPlaceId($this->targetRoom, 4);

        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
        Door::createFromRooms($this->startRoom, $this->adjacentRoom2);
        Door::createFromRooms($this->adjacentRoom1, $this->targetRoom);
        Door::createFromRooms($this->adjacentRoom2, $this->targetRoom);

        StatusFactory::createStatusByNameForHolder(StatusEnum::FIRE, $this->targetRoom);
    }

    private function givenAComplexRoomLayout(): void
    {
        // Create more complex room layout where there are multiple paths
        // to the target but one is shorter
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        $this->adjacentRoom2 = Place::createRoomByName('room3');
        $this->setPlaceId($this->adjacentRoom2, 3);

        $this->targetRoom = Place::createRoomByName('room4');
        $this->setPlaceId($this->targetRoom, 4);

        $this->distantRoom = Place::createRoomByName('room5');
        $this->setPlaceId($this->distantRoom, 5);

        // Path 1: start -> adjacent1 -> target -> distant
        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
        Door::createFromRooms($this->adjacentRoom1, $this->targetRoom);
        Door::createFromRooms($this->targetRoom, $this->distantRoom);

        // Path 2: start -> adjacent2 -> distant
        Door::createFromRooms($this->startRoom, $this->adjacentRoom2);
        Door::createFromRooms($this->adjacentRoom2, $this->distantRoom);
    }

    private function givenARoomLayoutWithIsolatedTarget(): void
    {
        $this->startRoom = Place::createRoomByName('room1');
        $this->setPlaceId($this->startRoom, 1);

        $this->adjacentRoom1 = Place::createRoomByName('room2');
        $this->setPlaceId($this->adjacentRoom1, 2);

        $this->targetRoom = Place::createRoomByName('isolated');
        $this->setPlaceId($this->targetRoom, 3);

        Door::createFromRooms($this->startRoom, $this->adjacentRoom1);
        // Target room is not connected to any room
    }

    private function givenTargetRoomHasFire(): void
    {
        StatusFactory::createStatusByNameForHolder(StatusEnum::FIRE, $this->targetRoom);
    }

    private function givenDistantRoomHasFire(): void
    {
        StatusFactory::createStatusByNameForHolder(StatusEnum::FIRE, $this->distantRoom);
    }

    private function givenStartRoomHasFire(): void
    {
        StatusFactory::createStatusByNameForHolder(StatusEnum::FIRE, $this->startRoom);
    }

    private function givenLastRoomHasFire(): void
    {
        StatusFactory::createStatusByNameForHolder(StatusEnum::FIRE, $this->targetRoom);
    }

    private function whenFindingClosestRoomWithCondition(callable $condition): ?Place
    {
        return $this->findNextRoomTowardsCondition->execute($this->startRoom, $condition);
    }

    private function setPlaceId(Place $place, int $id): void
    {
        (new \ReflectionProperty($place, 'id'))->setValue($place, $id);
    }
}
