<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case AMBITIOUS = 'ambitious';
    case CHUN_DEAD = 'chun_dead';
    case CHUN_LIVES = 'chun_lives';
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case EXPEDITION = 'expedition';
    case EXPLORATOR = 'explorator';
    case HUMANOCIDE = 'humanocide';
    case HUMANOCIDE_CAT = 'humanocide_cat';
    case KUBE_SOLVED = 'kube_solved';
    case MAGELLAN_ARK = 'magellan_ark';
    case MUSH_FEAR = 'mush_fear';
    case MUSH_INITIAL_BONUS = 'mush_initial_bonus';
    case MUSH_SPECIALIST = 'mush_specialist';
    case MUSH_VICTORY = 'mush_victory';
    case MUSHICIDE = 'mushicide';
    case MUSHICIDE_CAT = 'mushicide_cat';
    case PILGRED_MOTHER = 'pilgred_mother';
    case PSYCHOCAT = 'psychocat';
    case PSYCHOPAT = 'psychopat';
    case PRECIOUS_BODY = 'precious_body';
    case RESEARCH_BRILLANT = 'research_brillant';
    case RESEARCH_SMALL = 'research_small';
    case RESEARCH_STANDARD = 'research_standard';
    case RETURN_TO_SOL = 'return_to_sol';
    case SOL_CONTACT = 'sol_contact';
    case SOL_MUSH_INVASION = 'sol_mush_invasion';
    case SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    case SUPER_NOVA = 'super_nova';
    case NONE = '';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
