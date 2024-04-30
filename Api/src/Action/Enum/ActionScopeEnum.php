<?php

namespace Mush\Action\Enum;

/**
 * Class enumerating the Action scopes
 * Scope describes how "far" an action provider can provide an action
 *
 * SELF: the action provider is the action target
 * INVENTORY: the action provider is in inventory
 * ROOM: the action provider is in room
 * TERMINAL: the action is provided if player is focussed on a terminal
 */
abstract class ActionScopeEnum
{
    public const string SELF = 'self';
    public const string INVENTORY = 'inventory';
    public const string ROOM = 'room';
    public const string TERMINAL = 'terminal';
}
