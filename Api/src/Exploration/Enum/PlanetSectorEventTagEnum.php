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
 * RANDOM_FIGHT : Fight Strength is randomly selected.
 * REWARD_STARMAP_33 : 33% to get a starmap shard instead of the regular reward.
 *
 * Special tag PREVENTED: used when event is replaced by another event (e.g. fight prevented by white flag, or kill_lost prevented by tracker skill.)
 * No sector should be directly set to this, this is not a type.
 */
final class PlanetSectorEventTagEnum
{
    public const string POSITIVE = 'positive';
    public const string NEUTRAL = 'neutral';
    public const string NEGATIVE = 'negative';
    public const string PREVENTED = 'prevented';
    public const string RANDOM_FIGHT = 'random_fight';
    public const string REWARD_STARMAP_33 = 'reward_starmap_33';

    public static function getRandomFightPower(): array
    {
        return [
            8,
            10,
            12,
            15,
            18,
            32,
        ];
    }
}
