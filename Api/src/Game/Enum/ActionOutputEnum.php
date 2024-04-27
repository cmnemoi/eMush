<?php

namespace Mush\Game\Enum;

abstract class ActionOutputEnum
{
    public const string ONE_SHOT = 'one_shot';
    public const string CRITICAL_FAIL = 'critical_fail';
    public const string FAIL = 'fail';
    public const string SUCCESS = 'success';
    public const string CRITICAL_SUCCESS = 'critical_success';
    public const string NO_FUEL = 'no_fuel';
    public const string ARACK_PREVENTS_TRAVEL = 'arack_prevents_travel';
}
