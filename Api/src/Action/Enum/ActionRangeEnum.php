<?php

namespace Mush\Action\Enum;

/**
 * Class enumerating the ActionConfig range
 * Scope describes how "far" an action provider can provide an action.
 * SELF: The actionProvider is the action target or applies on the action target (eg a status on an equipment)
 * PLAYER: The action provider is bore by the player
 * ROOM: The actionProvider have an effect in all the room whether it is in player inventory or in the shelf
 * SHELF: The actionProvider have an effect in all the room if it is on the shelf.
 */
enum ActionRangeEnum: string
{
    case SELF = 'self';
    case PLAYER = 'player';
    case ROOM = 'room';
    case SHELF = 'shelf';
    case NULL = '';
}
