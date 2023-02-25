<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the different way a eventVariableModifier can be applied.
 *
 * ADDITIVE: outputValue = inputValue + delta
 * MULTIPLICATIVE: outputValue = inputValue * delta
 * SET_VALUE: outputValue = delta
 */
class VariableModifierModeEnum
{
    public const ADDITIVE = 'additive';
    public const MULTIPLICATIVE = 'multiplicative';
    public const SET_VALUE = 'set_value';

    public const VALUE = 'value';
    public const MIN = 'min';
    public const MAX = 'max';
}
