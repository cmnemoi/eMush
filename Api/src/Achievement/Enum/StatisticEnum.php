<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum StatisticEnum: string
{
    case EXTINGUISH_FIRE = 'extinguish_fire';
    case PLANET_SCANNED = 'planet_scanned';
    case SIGNAL_FIRE = 'signal_fire';
    case SIGNAL_EQUIP = 'signal_equip';
    case NULL = '';
}
