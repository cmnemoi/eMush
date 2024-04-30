<?php

declare(strict_types=1);

namespace Mush\Action\Enum;

abstract class ActionTargetName: string
{
    public const string PLAYER = 'player';
    public const string OTHER_PLAYER = 'other_player';
    public const string DOOR = 'door';
    public const string ITEM = 'item';
    public const string EQUIPMENT = 'equipment';
    public const string HUNTER = 'hunter';
    public const string TERMINAL = 'terminal';
    public const string PLANET = 'planet';
    public const string PROJECT = 'project';
}
