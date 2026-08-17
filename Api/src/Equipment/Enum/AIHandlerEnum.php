<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum AIHandlerEnum: string
{
    case NOTHING = 'nothing';
    case CAT = 'cat';
    case DRONE = 'drone';
    case PAVLOV = 'pavlov';
    case DRONE_EVIL = 'drone_evil';
    case CHICKEN = 'chicken';
    case BABY_SKINNER = 'baby_skinner';
}
