<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the possible target on which to apply a new event.
 */
abstract class EventTargetNameEnum
{
    public const string PLAYER = 'player';
    public const string DAEDALUS = 'daedalus';
    public const string EQUIPMENT = 'equipment';

    public const string EXCLUDE_PROVIDER = 'exclude_provider';
    public const string SINGLE_RANDOM = 'single_random';
}
