<?php

namespace Mush\Game\Enum;

abstract class EventEnum
{
    public const string NEW_CYCLE = 'new_cycle';
    public const string NEW_DAY = 'new_day';
    public const string CREATE_DAEDALUS = 'create-daedalus';
    public const string PLAYER_DEATH = 'player_death';
    public const string OUT_OF_CHARGE = 'out_of_charge';
    public const string FIRE = 'fire';
    public const string PLANT_PRODUCTION = 'plant_production';
    public const string NEW_MESSAGE = 'new_message';
}
