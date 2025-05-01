<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case HUMAN_TARGET = 'human_target';
    case MUSH_TARGET = 'mush_target';
    case PERSONAL = 'personal';

    case NULL = '';
}
