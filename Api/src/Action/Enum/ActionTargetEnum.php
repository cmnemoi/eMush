<?php

namespace Mush\Action\Enum;

abstract class ActionTargetEnum
{
    public const string EQUIPMENT = 'equipment'; // include item
    public const string ITEM = 'item';
    public const string DOOR = 'door';
    public const string TARGET_PLAYER = 'target_player';
    public const string SELF_PLAYER = 'self_player';
}
