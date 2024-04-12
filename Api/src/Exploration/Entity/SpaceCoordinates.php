<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Exploration\Enum\SpaceOrientationEnum;

final class SpaceCoordinates
{
    private string $orientation;
    private int $distance;

    public function __construct(string $orientation, int $distance)
    {
        $this->orientation = $orientation;
        $this->distance = $distance;
    }

    public static function getAll(): ArrayCollection
    {
        return new ArrayCollection([
            new self(SpaceOrientationEnum::NORTH, 2),
            new self(SpaceOrientationEnum::NORTH, 3),
            new self(SpaceOrientationEnum::NORTH, 4),
            new self(SpaceOrientationEnum::NORTH, 5),
            new self(SpaceOrientationEnum::NORTH, 6),
            new self(SpaceOrientationEnum::NORTH, 7),
            new self(SpaceOrientationEnum::NORTH, 8),
            new self(SpaceOrientationEnum::NORTH, 9),
            new self(SpaceOrientationEnum::WEST, 2),
            new self(SpaceOrientationEnum::WEST, 3),
            new self(SpaceOrientationEnum::WEST, 4),
            new self(SpaceOrientationEnum::WEST, 5),
            new self(SpaceOrientationEnum::WEST, 6),
            new self(SpaceOrientationEnum::WEST, 7),
            new self(SpaceOrientationEnum::WEST, 8),
            new self(SpaceOrientationEnum::WEST, 9),
            new self(SpaceOrientationEnum::SOUTH, 2),
            new self(SpaceOrientationEnum::SOUTH, 3),
            new self(SpaceOrientationEnum::SOUTH, 4),
            new self(SpaceOrientationEnum::SOUTH, 5),
            new self(SpaceOrientationEnum::SOUTH, 6),
            new self(SpaceOrientationEnum::SOUTH, 7),
            new self(SpaceOrientationEnum::SOUTH, 8),
            new self(SpaceOrientationEnum::SOUTH, 9),
            new self(SpaceOrientationEnum::EAST, 2),
            new self(SpaceOrientationEnum::EAST, 3),
            new self(SpaceOrientationEnum::EAST, 4),
            new self(SpaceOrientationEnum::EAST, 5),
            new self(SpaceOrientationEnum::EAST, 6),
            new self(SpaceOrientationEnum::EAST, 7),
            new self(SpaceOrientationEnum::EAST, 8),
            new self(SpaceOrientationEnum::EAST, 9),
        ]);
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function equals(self $spaceCoordinates): bool
    {
        return $this->orientation === $spaceCoordinates->orientation && $this->distance === $spaceCoordinates->distance;
    }
}
