<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case ALL_ACTIVE_HUMANS = 'all_active_humans';
    case ALL_ALIVE_MUSHS = 'all_alive_mushs';
    case ALL_ALIVE_HUMANS = 'all_alive_humans';
    case ALL_ALIVE_PLAYERS = 'all_alive_players';
    case ALL_MUSHS = 'all_mushs';
    case ALL_ACTIVE_EXPLORERS = 'all_active_explorers';
    case ALL_ACTIVE_HUMAN_EXPLORERS = 'all_active_human_explorers';

    case NONE = '';
}
