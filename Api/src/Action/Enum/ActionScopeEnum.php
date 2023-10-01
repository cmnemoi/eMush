<?php

namespace Mush\Action\Enum;

/**
 * Class enumerating the Action scopes
 * Scope describe the relation between the active player and the entity that provide the action.
 *
 * SELF: the action is provided by the active player
 * CURRENT: the action is provided by a game equipment (e.g. take action is proposed when targeting an item)
 * OTHERPLAYER: the action is provided by a targeted character (different from the active player)
 * INVENTORY: the action is provided by a tool (tool effect only apply on hold item)
 * ROOM: the action is provided by a tool (either in inventory or in the room)
 * SHELVE: the action is provided by a tool (tool effect only apply on equipment on the shelf)
 * TERMINAL: the action is provided if player is focused on a terminal
 */
class ActionScopeEnum
{
    public const SELF = 'self';
    public const CURRENT = 'current';
    public const OTHER_PLAYER = 'other_player';
    public const INVENTORY = 'inventory';
    public const ROOM = 'room';
    public const SHELVE = 'shelve';
    public const TERMINAL = 'terminal';
}
