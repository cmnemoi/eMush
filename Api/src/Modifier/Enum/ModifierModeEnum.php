<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the different way a modifier can be applied.
 *
 * ADDITIVE: outputValue = inputValue + delta
 * MULTIPLICATIVE: outputValue = inputValue * delta
 * SET_VALUE: outputValue = delta
 */
class ModifierModeEnum
{
    public const ADDITIVE = 'additive';
    public const MULTIPLICATIVE = 'multiplicative';
    public const SET_VALUE = 'set_value';
}
