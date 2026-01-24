<?php

namespace Mush\Disease\Enum;

enum DisorderEnum: string
{
    case AGORAPHOBIA = 'agoraphobia';
    case AILUROPHOBIA = 'ailurophobia';
    case CHRONIC_MIGRAINE = 'chronic_migraine';
    case CHRONIC_VERTIGO = 'chronic_vertigo';
    case COPROLALIA = 'coprolalia';
    case CRABISM = 'crabism';
    case DEPRESSION = 'depression';
    case PARANOIA = 'paranoia';
    case PSYCHOTIC_EPISODE = 'psychotic_episodes';
    case SPLEEN = 'spleen';
    case VERTIGO = 'vertigo';
    case WEAPON_PHOBIA = 'weapon_phobia';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
