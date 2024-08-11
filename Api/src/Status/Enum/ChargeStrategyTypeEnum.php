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
 * SKILL_POINTS_INCREMENT: charge increase by floor(maxCharge / 2) at each cycle if below the maximum charge
 * COFFEE_MACHINE_CHARGE_INCREMENT: charge increase by 1 every cycle if PILGRED is completed, else 1 on cycle 1
 * DAILY_DECREMENT_RESET: on cycle 1 the charge is set to the min amount
 * NONE: charge do not change with cycle or days
 */
abstract class ChargeStrategyTypeEnum
{
    public const string DAILY_DECREMENT = 'daily_decrement';
    public const string DAILY_INCREMENT = 'daily_increment';
    public const string CYCLE_INCREMENT = 'cycle_increment';
    public const string CYCLE_DECREMENT = 'cycle_decrement';
    public const string GROWING_PLANT = 'growing_plant';
    public const string DAILY_RESET = 'daily_reset';
    public const string NONE = 'none';
    public const string NO_DISCHARGE = 'no_discharge';
    public const string PATROL_SHIP_CHARGE_INCREMENT = 'patrol_ship_charge_increment';
    public const string SKILL_POINTS_INCREMENT = 'skill_points_increment';
    public const string COFFEE_MACHINE_CHARGE_INCREMENT = 'coffee_machine_charge_increment';
    public const string DAILY_DECREMENT_RESET = 'daily_decrement_reset';
}
