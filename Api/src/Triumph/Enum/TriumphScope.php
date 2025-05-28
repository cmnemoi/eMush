<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case ALL_ACTIVE_HUMANS = 'all_active_humans';
    case ALL_ALIVE_MUSHS = 'all_alive_mushs';
    case ALL_ALIVE_HUMANS = 'all_alive_humans';
    case ALL_MUSHS = 'all_mushs';

    /** Use to target a specific character (defined in the triumph config `target` attribute) */
    case PERSONAL = 'personal';

    case NONE = '';
}
