<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphEnum: string
{
    case CYCLE_HUMAN = 'cycle_human';
    case CYCLE_MUSH = 'cycle_mush';
    case CHUN_LIVES = 'chun_lives';
    case RETURN_TO_SOL = 'return_to_sol';
    case NULL = '';

    public function toConfigKey(string $configKey): string
    {
        return $this->value . '_' . $configKey;
    }
}
