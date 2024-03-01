<?php

namespace Mush\Modifier\Enum;

/**
 * Class enumerating the activationRequirements applied on modifiers
 * GameModifier is only applied if the activationRequirement is valid.
 *
 * RANDOM: the application of the modifier is random
 *
 * PLAYER_IN_ROOM: the activationRequirement is applied on the number of player in the room
 * ALONE: the player is alone in the room
 * NOT_ALONE: the player is not alone in the room
 * FOUR_PEOPLE: there is 4 players in the room
 * MUSH_IN_ROOM: there is a mush in the room
 *
 * ITEM_IN_ROOM: the activationRequirement is applied if item is in the room
 *
 * CYCLE: activationRequirement on the cycle (number, even)
 * EVEN: in case of a CYCLE activationRequirement, check if the cycle is even
 *
 * PLAYER_EQUIPMENT: the activationRequirement is applied on the player equipment
 *
 * STATUS: the activationRequirement is applied on the status of the modifier holder
 *
 * STATUS_NAME: the activation requirement is applied on the name of the charge status
 * STATUS_HOLDER_NAME: the activation requirement is applied on the name of the status holder
 */
class ModifierRequirementEnum
{
    public const RANDOM = 'random';

    public const PLAYER_IN_ROOM = 'player_in_room';
    public const ALONE = 'alone';
    public const NOT_ALONE = 'not_alone';
    public const FOUR_PEOPLE = 'four_people';
    public const MUSH_IN_ROOM = 'mush_in_room';

    public const ITEM_IN_ROOM = 'item_in_room';

    public const CYCLE = 'cycle';
    public const EVEN = 'even';

    public const PLAYER_EQUIPMENT = 'player_equipment';

    public const STATUS = 'status';

    public const ANY_TAGS = 'any_tags';
    public const NONE_TAGS = 'none_tags';
    public const ALL_TAGS = 'all_tags';
}
