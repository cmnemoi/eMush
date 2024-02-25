<?php

namespace Mush\Modifier\Enum;

/**
 * List priority as human-readable strings
 * Priority can also be set as a number e.g. '4'.
 */
class ModifierPriorityEnum
{
    public const INITIAL_SET_VALUE = 'initialSetValuePriority';
    public const ATTEMPT_INCREASE = 'attempt_increase';
    public const MULTIPLICATIVE_MODIFIER_VALUE = 'multiplicativeModifierPriority';
    public const ADDITIVE_MODIFIER_VALUE = 'additiveModifierPriority';
    public const OVERRIDE_VALUE_PRIORITY = 'overrideValuePriority';
    public const BEFORE_INITIAL_EVENT = 'beforeInitialEvent';
    public const PREVENT_EVENT = 'preventEventPriority';
    public const INITIAL_EVENT = 'initialEventPriority';
    public const AFTER_INITIAL_EVENT = 'afterInitialEvent';

    public const PRIORITY_MAP = [
        self::BEFORE_INITIAL_EVENT => -100,
        self::PREVENT_EVENT => -50,
        self::INITIAL_SET_VALUE => -20,
        self::ATTEMPT_INCREASE => -17,
        self::MULTIPLICATIVE_MODIFIER_VALUE => -15,
        self::ADDITIVE_MODIFIER_VALUE => -10,
        self::OVERRIDE_VALUE_PRIORITY => -5,
        self::INITIAL_EVENT => 0,
        self::AFTER_INITIAL_EVENT => 1,
    ];

    public const PRE_MODIFICATION = [self::BEFORE_INITIAL_EVENT];
    public const SIMULTANEOUS_MODIFICATION = [
        self::PREVENT_EVENT,
        self::ATTEMPT_INCREASE,
        self::INITIAL_SET_VALUE,
        self::MULTIPLICATIVE_MODIFIER_VALUE,
        self::ADDITIVE_MODIFIER_VALUE,
        self::OVERRIDE_VALUE_PRIORITY,
        self::INITIAL_EVENT,
    ];
    public const POST_MODIFICATION = [self::AFTER_INITIAL_EVENT];

    public static function getPriorityAsInteger(string $priority): int
    {
        return self::PRIORITY_MAP[$priority];
    }
}
