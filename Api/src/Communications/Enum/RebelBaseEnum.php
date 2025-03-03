<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum RebelBaseEnum: string
{
    case NULL = '';
    case WOLF = 'wolf';
    case SIRIUS = 'sirius';
    case CENTAURI = 'centauri';
    case KALADAAN = 'kaladaan';
    case LUYTEN_CETI = 'luyten_ceti';
    case UNKNOWN = 'unknown';

    public function toString(): string
    {
        return $this->value;
    }
}
