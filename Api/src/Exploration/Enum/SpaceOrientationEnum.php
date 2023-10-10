<?php

declare(strict_types=1);

namespace Mush\Exploration\Enum;

final class SpaceOrientationEnum
{
    public const NORTH = 'north';
    public const SOUTH = 'south';
    public const EAST = 'east';
    public const WEST = 'west';

    public static function getAll(): array
    {
        return [
            self::NORTH,
            self::SOUTH,
            self::EAST,
            self::WEST,
        ];
    }
}
