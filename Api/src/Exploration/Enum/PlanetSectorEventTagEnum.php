<?php

declare(strict_types=1);

namespace Mush\Exploration\Enum;

/** @codeCoverageIgnore */
/**
 * Class enumerating Sector Event Types
 * Used to determine what events are affected by weight changes of things like perks and items
 * POSITIVE: Event is positive for the human team (e.g. fruit harvest)
 * NEUTRAL: Event is neither good nor bad (e.g. Nothing to Report in most situations)
 * NEGATIVE: Event is negative for the human team (e.g. Accident).
 */
final class PlanetSectorEventTagEnum
{
    public const string POSITIVE = 'positive';
    public const string NEUTRAL = 'neutral';
    public const string NEGATIVE = 'negative';
}
