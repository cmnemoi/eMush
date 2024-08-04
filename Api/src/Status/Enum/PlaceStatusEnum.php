<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

enum PlaceStatusEnum: string
{
    case CEASEFIRE = 'ceasefire';
    case MUSH_TRAPPED = 'mush_trapped';

    public function toString(): string
    {
        return $this->value;
    }
}
