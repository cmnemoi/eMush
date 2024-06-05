<?php

declare(strict_types=1);

namespace Mush\Game\Enum;

enum GameVariableHolderEnum: string
{
    case DAEDALUS = 'daedalus';
    case HUNTER = 'hunter';
    case PLAYER = 'player';
    case ACTION_CONFIG = 'action_config';
    case STATUS = 'status';
    case PROJECT = 'project';
}