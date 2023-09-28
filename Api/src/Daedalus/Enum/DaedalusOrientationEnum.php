<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

final class DaedalusOrientationEnum
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