<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

use Mush\Equipment\Enum\ItemEnum;

abstract class DaedalusVariableEnum
{
    public const HULL = 'hull';
    public const OXYGEN = 'oxygen';
    public const FUEL = 'fuel';
    public const SHIELD = 'shield';
    public const SPORE = 'spore';
    public const HUNTER_POINTS = 'hunter_points';
    public const COMBUSTION_CHAMBER_FUEL = 'combustion_chamber_fuel';

    public static function toOfferedTradeItem(string $variable): string
    {
        return match ($variable) {
            self::OXYGEN => ItemEnum::OXYGEN_CAPSULE,
            self::FUEL => ItemEnum::FUEL_CAPSULE,
            default => throw new \RuntimeException("{$variable} does not have a corresponding trade item, or is not a Daedalus variable!"),
        };
    }
}
