<?php

declare(strict_types=1);

namespace Mush\Equipment\Enum;

enum DroneTaskEnum: string
{
    case MOVE_IN_RANDOM_ADJACENT_ROOM = 'move_in_random_adjacent_room';
    case REPAIR_BROKEN_EQUIPMENT = 'repair_broken_equipment';
}
