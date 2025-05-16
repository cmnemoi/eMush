<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case ALL_ACTIVE_HUMANS = 'all_active_humans';
    case ALL_ALIVE_HUMANS = 'all_alive_humans';
    case ALL_MUSHS = 'all_mushs';
    case PERSONAL = 'personal';

    case NULL = '';
}
