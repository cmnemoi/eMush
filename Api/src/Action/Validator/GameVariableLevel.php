<?php

namespace Mush\Action\Validator;

/**
 * Raises a violation if a GameVariable does not meet the specified conditions.
 *
 * @param string $variableName the name of the variable to check
 * @param string $target       the target of the variable to check (daedalus, player or target_player)
 * @param string $checkMode    the criteria of the check (is_min, is_max, is_in_range, equals)
 * @param int    $value        the value to compare the variable to, if using equals checkMode
 */
class GameVariableLevel extends ClassConstraint
{
    public const string DAEDALUS = 'daedalus';
    public const string PLAYER = 'player';
    public const string TARGET_PLAYER = 'target_player';

    public const string IS_MIN = 'is_min';
    public const string IS_MAX = 'is_max';
    public const string IS_IN_RANGE = 'is_in_range';

    public const string EQUALS = 'equals';

    public string $message = 'this daedalus variable cannot be changed';

    public string $target = self::DAEDALUS;

    public string $checkMode = self::IS_MAX;

    public string $variableName;

    public int $value;
}
