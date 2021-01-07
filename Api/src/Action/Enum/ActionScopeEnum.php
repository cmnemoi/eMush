<?php

namespace Mush\Action\Enum;

class ActionScopeEnum
{
    public const SELF = 'self';
    public const CURRENT = 'current';
    public const OTHER_PLAYER = 'other_player';
    public const INVENTORY = 'inventory';
    public const DOOR = 'door';
    public const ROOM = 'room';
    public const SHELVE = 'shelve';
}
