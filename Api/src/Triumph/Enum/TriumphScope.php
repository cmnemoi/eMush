<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case ALL_ALIVE_MUSHS = 'all_alive_mushs';
    case ALL_ALIVE_HUMANS = 'all_alive_humans';
    case ALL_ALIVE_PLAYERS = 'all_alive_players';
    case ALL_MUSHS = 'all_mushs';
    case ALL_PLAYERS = 'all_players';
    case ALL_ALIVE_HUMAN_PHARMACISTS = 'all_alive_human_pharmacists';
    case ALL_ALIVE_HUMAN_TECHNICIANS = 'all_alive_human_technicians';

    // Personal triumph
    case PERSONAL_ANDIE = 'personal_andie';
    case PERSONAL_CHAO = 'personal_chao';
    case PERSONAL_CHUN = 'personal_chun';
    case PERSONAL_DEREK = 'personal_derek';
    case PERSONAL_ELEESHA = 'personal_eleesha';
    case PERSONAL_FINOLA = 'personal_finola';
    case PERSONAL_FRIEDA = 'personal_frieda';
    case PERSONAL_GIOELE = 'personal_gioele';
    case PERSONAL_HUA = 'personal_hua';
    case PERSONAL_IAN = 'personal_ian';
    case PERSONAL_JANICE = 'personal_janice';
    case PERSONAL_JIN_SU = 'personal_jin_su';
    case PERSONAL_KUAN_TI = 'personal_kuan_ti';
    case PERSONAL_PAOLA = 'personal_paola';
    case PERSONAL_RALUCA = 'personal_raluca';
    case PERSONAL_ROLAND = 'personal_roland';
    case PERSONAL_STEPHEN = 'personal_stephen';
    case PERSONAL_TERRENCE = 'personal_terrence';

    case NONE = '';

    // deprecated
    case ALL_ACTIVE_HUMANS = 'all_active_humans';

    public function toString(): string
    {
        return $this->value;
    }
}
