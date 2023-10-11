<?php

declare(strict_types=1);

namespace Mush\Exploration\Enum;

use Doctrine\Common\Collections\ArrayCollection;

/** @codeCoverageIgnore */
final class PlanetSectorEnum
{
    public const CAVE = 'cave';
    public const COLD = 'cold';
    public const CRISTAL_FIELD = 'cristal_field';
    public const DESERT = 'desert';
    public const FOREST = 'forest';
    public const FRUIT_TREES = 'fruit_trees';
    public const HOT = 'hot';
    public const HYDROCARBON = 'hydrocarbon';
    public const INSECT = 'insect';
    public const INTELLIGENT = 'intelligent';
    public const LANDING = 'landing';
    public const LOST = 'lost';
    public const MANKAROG = 'mankarog';
    public const MOUNTAIN = 'mountain';
    public const OCEAN = 'ocean';
    public const OXYGEN = 'oxygen';
    public const PREDATOR = 'predator';
    public const RUINS = 'ruins';
    public const RUMINANT = 'ruminant';
    public const SISMIC_ACTIVITY = 'sismic_activity';
    public const STRONG_WIND = 'strong_wind';
    public const SWAMP = 'swamp';
    public const UNKNOWN = 'unknown';
    public const VOLCANIC_ACTIVITY = 'volcanic_activity';
    public const WRECK = 'wreck';

    public static function getAll(): ArrayCollection
    {
        return new ArrayCollection([
            self::CAVE,
            self::COLD,
            self::CRISTAL_FIELD,
            self::DESERT,
            self::FOREST,
            self::FRUIT_TREES,
            self::HOT,
            self::HYDROCARBON,
            self::INSECT,
            self::INTELLIGENT,
            self::MANKAROG,
            self::MOUNTAIN,
            self::OCEAN,
            self::OXYGEN,
            self::PREDATOR,
            self::RUINS,
            self::RUMINANT,
            self::SISMIC_ACTIVITY,
            self::STRONG_WIND,
            self::SWAMP,
            self::VOLCANIC_ACTIVITY,
            self::WRECK,
        ]);
    }
}
