<?php

namespace Mush\Disease\Enum;

/**
 * Class enumerating the statuses of the diseases.
 *
 * INCUBATING: the disease is incubating, it is invisible to anyone and have no effect on player. It cannot be cured
 * ACTIVE: the disease is active, it is visible by the player, have effect and can be cured
 */
abstract class DiseaseStatusEnum
{
    public const string INCUBATING = 'incubating';
    public const string ACTIVE = 'active';

    // Cure
    public const string SPONTANEOUS_CURE = 'spontaneous_cure';
    public const string MUSH_CURE = 'mush_cure';
    public const string HEALED = 'healed';
    public const string DRUG_HEALED = 'healed';
}
