<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum XylophEnum: string
{
    case DISK = 'disk';
    case GHOST_CHUN = 'ghost_chun';
    case GHOST_SAMPLE = 'ghost_sample';
    case KIVANC = 'kivanc';
    case MAGNETITE = 'magnetite';
    case NOTHING = 'nothing';
    case NULL = '';
    case SNOW = 'snow';
    case UNKNOWN = 'unknown';
    case VERSION = 'version';

    public function toString(): string
    {
        return $this->value;
    }
}
