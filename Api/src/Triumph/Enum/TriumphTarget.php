<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphTarget: string
{
    case STATUS_HOLDER = 'status_holder';
    case NONE = 'none';

    public function toString(): string
    {
        return $this->value;
    }
}
