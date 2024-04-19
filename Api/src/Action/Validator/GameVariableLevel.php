<?php

namespace Mush\Action\Validator;

class GameVariableLevel extends ClassConstraint
{
    public const string DAEDALUS = 'daedalus';
    public const string PLAYER = 'player';
    public const string TARGET_PLAYER = 'target_player';

    public const string IS_MIN = 'is_min';
    public const string IS_MAX = 'is_max';
    public const string IS_IN_RANGE = 'is_in_range';

    public string $message = 'this daedalus variable cannot be changed';

    public string $target = self::DAEDALUS;

    public string $checkMode = self::IS_MAX;

    public string $variableName;
}
