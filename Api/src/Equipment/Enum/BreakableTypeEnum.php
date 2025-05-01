<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum BreakableTypeEnum: string
{
    case NONE = 'none';
    case BREAKABLE = 'breakable';
    case DESTROY_ON_BREAK = 'destroy_on_break';
}
