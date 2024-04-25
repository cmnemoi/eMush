<?php

declare(strict_types=1);

namespace Mush\Action\Enum;

enum ActionTargetName: string
{
    case PLAYER = 'player';
    case DOOR = 'door';
    case ITEM = 'item';
    case EQUIPMENT = 'equipment';
    case HUNTER = 'hunter';
    case TERMINAL = 'terminal';
    case PLANET = 'planet';
    case PROJECT = 'project';
}
