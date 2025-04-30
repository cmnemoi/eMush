<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphVisibility: string
{
    case PRIVATE = 'private';
    case HIDDEN = 'hidden';
    case NULL = '';

    public function toString(): string
    {
        return $this->value;
    }
}
