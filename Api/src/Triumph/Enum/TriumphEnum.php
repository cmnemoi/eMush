<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case CHUN_LIVES = 'chun_lives';
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case EXPEDITION = 'expedition';
    case EXPLORATOR = 'explorator';
    case KUBE_SOLVED = 'kube_solved';
    case MAGELLAN_ARK = 'magellan_ark';
    case MUSH_INITIAL_BONUS = 'mush_initial_bonus';
    case MUSH_SPECIALIST = 'mush_specialist';
    case PRECIOUS_BODY = 'precious_body';
    case RESEARCH_BRILLANT = 'research_brillant';
    case RESEARCH_SMALL = 'research_small';
    case RESEARCH_STANDARD = 'research_standard';
    case RETURN_TO_SOL = 'return_to_sol';
    case SOL_CONTACT = 'sol_contact';
    case SOL_MUSH_INVASION = 'sol_mush_invasion';
    case SOL_MUSH_INTRUDER = 'sol_mush_intruder';
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
