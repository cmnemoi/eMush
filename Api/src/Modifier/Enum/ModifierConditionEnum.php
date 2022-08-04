<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the conditions applied on modifiers
 * Modifier is only applied if the condition is valid.
 *
 * REASON: check on the reason that triggered the event
 * RANDOM: the application of the modifier is random
 *
 * PLAYER_IN_ROOM: the condition is applied on the number of player in the room
 * ALONE: the player is alone in the room
 * NOT_ALONE: the player is not alone in the room
 *
 * CYCLE: condition on the cycle (number, even)
 * EVEN: in case of a CYCLE condition, check if the cycle is even
 *
 * PLAYER_EQUIPMENT: the condition is applied on the player equipment
 * HOLD_SCHRODINGER: the player is holding schrodinger
 */
class ModifierConditionEnum
{
    public const REASON = 'reason';
    public const RANDOM = 'random';

    public const PLAYER_IN_ROOM = 'player_in_room';
    public const ALONE = 'alone';
    public const NOT_ALONE = 'not_alone';

    public const CYCLE = 'cycle';
    public const EVEN = 'even';

    public const PLAYER_EQUIPMENT = 'player_equipment';
    public const HOLD_SCHRODINGER = 'hold_schrodinger';
}
