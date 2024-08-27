<?php

declare(strict_types=1);

namespace Mush\Exploration\Enum;

use Doctrine\Common\Collections\ArrayCollection;

/** @codeCoverageIgnore */
final class PlanetSectorEnum
{
    public const string CAVE = 'cave';
    public const string COLD = 'cold';
    public const string CRISTAL_FIELD = 'cristal_field';
    public const string DESERT = 'desert';
    public const string FOREST = 'forest';
    public const string FRUIT_TREES = 'fruit_trees';
    public const string HOT = 'hot';
    public const string HYDROCARBON = 'hydrocarbon';
    public const string INSECT = 'insect';
    public const string INTELLIGENT = 'intelligent';
    public const string LANDING = 'landing';
    public const string LOST = 'lost';
    public const string MANKAROG = 'mankarog';
    public const string MOUNTAIN = 'mountain';
    public const string OCEAN = 'ocean';
    public const string OXYGEN = 'oxygen';
    public const string PREDATOR = 'predator';
    public const string RUINS = 'ruins';
    public const string RUMINANT = 'ruminant';
    public const string SEISMIC_ACTIVITY = 'seismic_activity';
    public const string STRONG_WIND = 'strong_wind';
    public const string SWAMP = 'swamp';
    public const string UNKNOWN = 'unknown';
    public const string VOLCANIC_ACTIVITY = 'volcanic_activity';
    public const string WRECK = 'wreck';

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
            self::SEISMIC_ACTIVITY,
            self::STRONG_WIND,
            self::SWAMP,
            self::VOLCANIC_ACTIVITY,
            self::WRECK,
        ]);
    }

    public static function getLifeForms(): ArrayCollection
    {
        return new ArrayCollection([
            self::INSECT,
            self::INTELLIGENT,
            self::LOST,
            self::MANKAROG,
            self::PREDATOR,
            self::RUMINANT,
        ]);
    }
}
