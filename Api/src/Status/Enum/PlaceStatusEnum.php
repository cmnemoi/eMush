<?php

declare(strict_types=1);

namespace Mush\Status\Enum;

enum PlaceStatusEnum: string
{
    case CEASEFIRE = 'ceasefire';
    case DELOGGED = 'delogged';
    case MUSH_TRAPPED = 'mush_trapped';
    case SELECTED_FOR_ELECTROCUTION = 'selected_for_electrocution';
    case SELECTED_FOR_JOLT = 'selected_for_jolt';
    case SELECTED_FOR_FIRE = 'selected_for_fire';

    public function toString(): string
    {
        return $this->value;
    }
}
