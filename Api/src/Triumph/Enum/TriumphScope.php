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

    // Personal triumph
    case PERSONAL_CHUN = 'personal_chun';
    case PERSONAL_FINOLA = 'personal_finola';
    case PERSONAL_HUA = 'personal_hua';
    case PERSONAL_KUAN_TI = 'personal_kuan_ti';
    case PERSONAL_RALUCA = 'personal_raluca';

    case NONE = '';
}
