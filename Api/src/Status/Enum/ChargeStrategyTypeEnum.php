<?php

namespace Mush\Status\Enum;

/**
 * Class enumerating the different charge strategies.
 *
 * DAILY_DECREMENT: charge decrease by 1 on cycle 1 with a minimum of 0
 * DAILY_INCREMENT: charge increase by 1 on cycle 1 if below the maximum charge
 * CYCLE_INCREMENT: charge increase by 1 every cycle if below the maximum charge
 * CYCLE_DECREMENT: charge decrease by 1 every cycle with a minimum of 0
 * GROWING_PLANT: charge increase by 1 every cycle
 * DAILY_RESET: on cycle 1 the charge is set to the max amount
 * PATROL_SHIP_CHARGE_INCREMENT: patrol ship charge increase by 1 every cycle if below the maximum charge and patrol ship is not in battle
 * SPECIALIST_POINTS_INCREMENT: charge increase by floor(maxCharge / 2) at each cycle if below the maximum charge
 * NONE: charge do not change with cycle or days
 */
class ChargeStrategyTypeEnum
{
    public const DAILY_DECREMENT = 'daily_decrement';
    public const DAILY_INCREMENT = 'daily_increment';
    public const CYCLE_INCREMENT = 'cycle_increment';
    public const CYCLE_DECREMENT = 'cycle_decrement';
    public const GROWING_PLANT = 'growing_plant';
    public const DAILY_RESET = 'daily_reset';
    public const NONE = 'none';
    public const NO_DISCHARGE = 'no_discharge';
    public const PATROL_SHIP_CHARGE_INCREMENT = 'patrol_ship_charge_increment';
    public const SPECIALIST_POINTS_INCREMENT = 'specialist_points_increment';
}
