<?php

namespace Mush\Disease\Enum;

abstract class DisorderEnum
{
    public const string AGORAPHOBIA = 'agoraphobia';
    public const string AILUROPHOBIA = 'ailurophobia';
    public const string CHRONIC_MIGRAINE = 'chronic_migraine';
    public const string CHRONIC_VERTIGO = 'chronic_vertigo';
    public const string COPROLALIA = 'coprolalia';
    public const string CRABISM = 'crabism';
    public const string DEPRESSION = 'depression';
    public const string PARANOIA = 'paranoia';
    public const string PSYCHOTIC_EPISODE = 'psychotic_episodes';
    public const string SPLEEN = 'spleen';
    public const string VERTIGO = 'vertigo';
    public const string WEAPON_PHOBIA = 'weapon_phobia';

    public static function getAllDisorders(): array
    {
        return [
            self::AGORAPHOBIA,
            self::AILUROPHOBIA,
            self::CHRONIC_MIGRAINE,
            self::CHRONIC_VERTIGO,
            self::COPROLALIA,
            self::CRABISM,
            self::DEPRESSION,
            self::PARANOIA,
            self::PSYCHOTIC_EPISODE,
            self::SPLEEN,
            self::VERTIGO,
            self::WEAPON_PHOBIA,
        ];
    }
}
