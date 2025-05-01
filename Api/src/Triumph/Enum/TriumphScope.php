<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphScope: string
{
    case ALL_HUMAN = 'all_human';
    case ALL_MUSH = 'all_mush';
    case PERSONAL = 'personal';

    case NULL = '';
}
