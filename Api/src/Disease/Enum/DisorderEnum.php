<?php

namespace Mush\Disease\Enum;

class DisorderEnum
{
    public const AGORAPHOBIA = 'agoraphobia';
    public const AILUROPHOBIA = 'ailurophobia';
    public const CHRONIC_MIGRAINE = 'chronic_migraine';
    public const CHRONIC_VERTIGO = 'chronic_vertigo';
    public const COPROLALIA = 'coprolalia';
    public const CRABISM = 'crabism';
    public const DEPRESSION = 'depression';
    public const PARANOIA = 'paranoia';
    public const PSYCOTIC_EPISODE = 'psychotic_episodes';
    public const SPLEEN = 'spleen';
    public const VERTIGO = 'vertigo';
    public const WEAPON_PHOBIA = 'weapon_phobia';

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
            self::PSYCOTIC_EPISODE,
            self::SPLEEN,
            self::VERTIGO,
            self::WEAPON_PHOBIA,
        ];
    }
}
