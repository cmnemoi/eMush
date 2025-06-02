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
    case ALL_PLAYERS = 'all_players';
    case ALL_ALIVE_HUMAN_PHARMACISTS = 'all_alive_human_pharmacists';
    case ALL_ALIVE_HUMAN_TECHNICIANS = 'all_alive_human_technicians';

    // Personal triumph
    case PERSONAL_CHAO = 'personal_chao';
    case PERSONAL_CHUN = 'personal_chun';
    case PERSONAL_FINOLA = 'personal_finola';
    case PERSONAL_GIOELE = 'personal_gioele';
    case PERSONAL_HUA = 'personal_hua';
    case PERSONAL_JANICE = 'personal_janice';
    case PERSONAL_JIN_SU = 'personal_jin_su';
    case PERSONAL_KUAN_TI = 'personal_kuan_ti';
    case PERSONAL_RALUCA = 'personal_raluca';
    case PERSONAL_STEPHEN = 'personal_stephen';

    case NONE = '';
}
