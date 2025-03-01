<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum RebelBaseEnum: string
{
    case NULL = '';
    case KALADAAN = 'kaladaan';
    case SIRIUS = 'sirius';
    case UNKNOWN = 'unknown';
    case WOLF = 'wolf';

    public function toString(): string
    {
        return $this->value;
    }
}
