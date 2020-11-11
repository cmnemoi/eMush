<?php

namespace Status\Enum;

class ChargeStrategyTypeEnum
{
    public const DAILY_USAGE = 'daily_usage';
    public const CYCLE_INCREMENT = 'cycle_increment';
    public const CYCLE_DECREMENT = 'cycle_decrement';
    public const CYCLE_FIXED = 'cycle_fixed';
    public const PLANT = 'plant';

    public static function getDayStrategies()
    {
        return [
            self::DAILY_USAGE,
        ];
    }

    public static function getCycleStrategies()
    {
        return [
            self::CYCLE_INCREMENT,
            self::CYCLE_DECREMENT,
            self::CYCLE_FIXED,
            self::PLANT,
        ];
    }
}
