<?php

namespace Mush\Action\Validator;

class GameVariableLevel extends ClassConstraint
{
    public const DAEDALUS = 'daedalus';
    public const PLAYER = 'player';
    public const TARGET_PLAYER = 'target_player';

    public const IS_MIN = 'is_min';
    public const IS_MAX = 'is_max';
    public const IS_IN_RANGE = 'is_in_range';

    public string $message = 'this daedalus variable cannot be changed';

    public string $target = self::DAEDALUS;

    public string $checkMode = self::IS_MAX;

    public string $variableName;
}
