<?php

declare(strict_types=1);

namespace Mush\Project\Enum;

enum ProjectRequirementType: string
{
    case CHUN_IN_LABORATORY = 'chun_in_laboratory';
    case MUSH_PLAYER_DEAD = 'mush_player_dead';
    case ITEM_IN_LABORATORY = 'item_in_laboratory';
    case ITEM_IN_PLAYER_INVENTORY = 'item_in_player_inventory';
}
