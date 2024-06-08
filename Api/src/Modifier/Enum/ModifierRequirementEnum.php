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
 * HOLDER_NAME: the activation requirement is applied on the name of the holder (used mostly for equipments)
 *
 * SKILL_IN_ROOM: the activationRequirement is applied if there is a player with the specified skill in the room
 *
 * HOLDER_PLACE: the activationRequirement is applied if holder is in the specified place
 *
 * The following conditions are used on EventModifiers, the tags of the event are compared with the tagConstraint of the modifier
 * ANY_TAG: The event must have at least ONE of the following tag
 * ALL_TAG: The event must have ALL the following tags
 * NONE_TAG: The event must have NONE of the following tags
 */
abstract class ModifierRequirementEnum
{
    public const string RANDOM = 'random';
    public const string PLAYER_IN_ROOM = 'player_in_room';
    public const string ALONE = 'alone';
    public const string NOT_ALONE = 'not_alone';
    public const string FOUR_PEOPLE = 'four_people';
    public const string MUSH_IN_ROOM = 'mush_in_room';
    public const string ITEM_IN_ROOM = 'item_in_room';
    public const string CYCLE = 'cycle';
    public const string EVEN = 'even';
    public const string PLAYER_EQUIPMENT = 'player_equipment';
    public const string HOLDER_HAS_STATUS = 'status';
    public const string HOLDER_NAME = 'holder_name';
    public const string SKILL_IN_ROOM = 'skill_in_room';
    public const string HOLDER_PLACE = 'holder_place';
    public const string ANY_TAGS = 'any_tags';
    public const string NONE_TAGS = 'none_tags';
    public const string ALL_TAGS = 'all_tags';
    public const int ABSENT_STATUS = 0;

    public const string HOLDER_PLACE_GARDEN = 'holder_place_garden';
}
