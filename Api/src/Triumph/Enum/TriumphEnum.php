<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case ALIEN_SCIENCE = 'alien_science';
    case AMBITIOUS = 'ambitious';
    case CHUN_DEAD = 'chun_dead';
    case CHUN_LIVES = 'chun_lives';
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case EDEN_AT_LEAST = 'eden_at_least';
    case EDEN_BIOLOGISTS = 'eden_biologists';
    case EDEN_ENGINEERS = 'eden_engineers';
    case EDEN_MUSH_INTRUDER = 'eden_mush_intruder';
    case EDEN_MUSH_INVASION = 'eden_mush_invasion';
    case EDEN_ONE_MAN = 'eden_one_man';
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
    case NICE_SURGERY = 'nice_surgery';
    case PILGRED_MOTHER = 'pilgred_mother';
    case PSYCHOCAT = 'psychocat';
    case PSYCHOPAT = 'psychopat';
    case PRECIOUS_BODY = 'precious_body';
    case REMEDY = 'remedy';
    case RESEARCH_BRILLANT = 'research_brillant';
    case RESEARCH_SMALL = 'research_small';
    case RESEARCH_STANDARD = 'research_standard';
    case RETURN_TO_SOL = 'return_to_sol';
    case ROBOTIC_GRAAL = 'robotic_graal';
    case SAVIOR = 'savior';
    case SOL_CONTACT = 'sol_contact';
    case SOL_MUSH_INVASION = 'sol_mush_invasion';
    case SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    case SUPER_NOVA = 'super_nova';
    case NONE = '';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }

    public function toLogKey(): string
    {
        return "{$this->value}.log";
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toEmoteCode(): string
    {
        return $this->isMushTriumph() ? ':triumph_mush:' : ':triumph:';
    }

    private function isMushTriumph(): bool
    {
        return \in_array($this, [
            self::CYCLE_MUSH,
            self::MUSH_INITIAL_BONUS,
            self::MUSH_VICTORY,
            self::SOL_MUSH_INVASION,
        ], true);
    }
}
