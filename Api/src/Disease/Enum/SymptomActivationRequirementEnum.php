<?php

namespace Mush\Disease\Enum;

/**
 * Class enumerating the activationRequirements applied on symptom
 * Symptom is only applied if the activationRequirement is valid.
 *
 * REASON: check on the reason that triggered the event
 *
 * RANDOM: the symptom is triggered as random
 *
 * PLAYER_IN_ROOM: the activationRequirement is applied based on players in the room
 * NOT_ALONE: the player is not alone in the room
 * THREE_OTHERS: the player is alone in the room with at least 3 other players
 * MUSH_IN_ROOM: the activationRequirement is applied is there is a Mush in room
 *
 * ITEM_IN_ROOM: the activationRequirement is applied if the item is in the room
 *
 * PLAYER_EQUIPMENT: the activationRequirement is checked on player's equipment
 *
 * ITEM_STATUS : the activationRequirement is checked on item status
 *
 * PLAYER_STATUS : the activationRequirement is checked on player status
 *
 * ACTION_DIRTY_RATE : the activationRequirement is checked on action dirty rate
 */
abstract class SymptomActivationRequirementEnum
{
    public const string REASON = 'reason';

    public const string RANDOM = 'random';

    public const string PLAYER_IN_ROOM = 'player_in_room';
    public const string NOT_ALONE = 'not_alone';
    public const string THREE_OTHERS = 'three_others';
    public const string MUSH_IN_ROOM = 'mush_in_room';

    public const string ITEM_IN_ROOM = 'item_in_room';

    public const string PLAYER_EQUIPMENT = 'player_equipment';

    public const string ITEM_STATUS = 'item_status';

    public const string PLAYER_STATUS = 'player_status';

    public const string ACTION_DIRTY_RATE = 'action_dirty_rate';
}
