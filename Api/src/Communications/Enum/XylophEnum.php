<?php

declare(strict_types=1);

namespace Mush\Communications\Enum;

enum XylophEnum: string
{
    case NULL = '';
    case NOTHING = 'nothing';
    case DISK = 'disk';
    case SNOW = 'snow';
    case MAGNETITE = 'magnetite';
    case VERSION = 'version';

    public function toString(): string
    {
        return $this->value;
    }
}
