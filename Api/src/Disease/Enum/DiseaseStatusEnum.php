<?php

namespace Mush\Disease\Enum;

/**
 * Class enumerating the statuses of the diseases.
 *
 * INCUBATING: the disease is incubating, it is invisible to anyone and have no effect on player. It cannot be cured
 * ACTIVE: the disease is active, it is visible by the player, have effect and can be cured
 */
class DiseaseStatusEnum
{
    public const INCUBATING = 'incubating';
    public const ACTIVE = 'active';

    // Cure
    public const SPONTANEOUS_CURE = 'spontaneous_cure';
    public const MUSH_CURE = 'mush_cure';
    public const HEALED = 'healed';
    public const DRUG_HEALED = 'healed';
}
