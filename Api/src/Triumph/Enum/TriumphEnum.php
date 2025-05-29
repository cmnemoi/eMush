<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case CHUN_LIVES = 'chun_lives';
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case MUSH_INITIAL_BONUS = 'mush_initial_bonus';
    case RETURN_TO_SOL = 'return_to_sol';
    case SOL_MUSH_INTRUDER = 'sol_mush_intruder';
    case EXPEDITION = 'expedition';
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
