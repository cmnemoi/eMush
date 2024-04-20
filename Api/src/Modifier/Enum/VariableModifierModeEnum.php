<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the different way a eventVariableModifier can be applied.
 *
 * ADDITIVE: outputValue = inputValue + delta
 * MULTIPLICATIVE: outputValue = inputValue * delta
 * SET_VALUE: outputValue = delta
 */
abstract class VariableModifierModeEnum
{
    public const string ADDITIVE = 'additive';
    public const string MULTIPLICATIVE = 'multiplicative';
    public const string SET_VALUE = 'set_value';
    public const string VALUE = 'value';
    public const string MIN = 'min';
    public const string MAX = 'max';
}
