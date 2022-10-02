<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the conditions applied on modifiers
 * Modifier is only applied if the condition is valid.
 *
 * REASON: the modifier is applied on a certain reason.
 *        For example, only on Move actions.
 *
 * NOT_REASON : the modifier is applied on every reason except the one specified.
 *            For example, on all actions except Move.
 *
 * RANDOM: the application of the modifier is random
 *
 * PLAYER_IN_ROOM: the condition is applied on the number of player in the room
 * ALONE: the player is alone in the room
 * NOT_ALONE: the player is not alone in the room
 * FOUR_PEOPLE: there is 4 players in the room
 *
 * ITEM_IN_ROOM: the condition is applied if item is in the room
 *
 * CYCLE: condition on the cycle (number, even)
 * EVEN: in case of a CYCLE condition, check if the cycle is even
 *
 * PLAYER_EQUIPMENT: the condition is applied on the player equipment
 *
 * PLAYER_STATUS: the condition is applied on the player status
 */
class ModifierConditionEnum
{
    public const REASON = 'reason';

    public const NOT_REASON = 'not_reason';

    public const RANDOM = 'random';

    public const PLAYER_IN_ROOM = 'player_in_room';
    public const ALONE = 'alone';
    public const NOT_ALONE = 'not_alone';
    public const FOUR_PEOPLE = 'four_people';

    public const ITEM_IN_ROOM = 'item_in_room';

    public const CYCLE = 'cycle';
    public const EVEN = 'even';

    public const PLAYER_EQUIPMENT = 'player_equipment';

    public const PLAYER_STATUS = 'player_status';
}
