<?php

namespace Mush\RoomLog\Enum;

class LogEnum
{
    public const GAIN_EXPERIENCE = 'gain_experience';
    public const GAIN_TRIUMPH = 'gain_triumph';
    public const EXIT_ROOM = 'exit_room';
    public const ENTER_ROOM = 'enter_room';
    public const EAT = 'eat';
    public const TAKE = 'take';
    public const DROP = 'drop';
    public const AWAKEN = 'awaken';
    public const DEATH = 'death';
    public const NEW_DAY = 'new_day';
    public const GAIN_ACTION_POINT = 'gain_action_point';
    public const LOSS_ACTION_POINT = 'loss_action_point';
    public const GAIN_MOVEMENT_POINT = 'gain_movement_point';
    public const LOSS_MOVEMENT_POINT = 'loss_movementpoint';
    public const GAIN_HEALTH_POINT = 'gain_health_point';
    public const LOSS_HEALTH_POINT = 'loss_health_point';
    public const GAIN_MORAL_POINT = 'gain_moral_point';
    public const LOSS_MORAL_POINT = 'loss_moral_point';
    public const SOILED = 'soiled';
    public const SOIL_PREVENTED = 'soil_prevented';
    public const SOIL_PREVENTED_OCD = 'soil_prevented_ocd';
}
