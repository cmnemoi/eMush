<?php

declare(strict_types=1);

namespace Mush\Triumph\Enum;

enum TriumphTeam: string
{
    case MUSH = 'mush';
    case HUMAN = 'human';
    case ANY = 'any';
    case NULL = '';
}
