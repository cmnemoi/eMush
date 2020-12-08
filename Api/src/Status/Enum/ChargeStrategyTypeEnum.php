<?php

namespace Mush\Status\Enum;

class ChargeStrategyTypeEnum
{
    public const DAILY_DECREMENT = 'daily_decrement';
    public const DAILY_INCREMENT = 'daily_increment';
    public const CYCLE_INCREMENT = 'cycle_increment';
    public const CYCLE_DECREMENT = 'cycle_decrement';
    public const GROWING_PLANT = 'growing_plant';
    public const NONE = 'none';
}
