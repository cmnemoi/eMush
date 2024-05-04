<?php

declare(strict_types=1);

namespace Mush\Action\Enum;

enum ActionHolderEnum: string
{
    case PLAYER = 'player';
    case OTHER_PLAYER = 'other_player';
    case EQUIPMENT = 'equipment';
    case HUNTER = 'hunter';
    case TERMINAL = 'terminal';
    case PLANET = 'planet';
    case PROJECT = 'project';
}
