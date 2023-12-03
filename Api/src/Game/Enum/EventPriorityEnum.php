<?php

declare(strict_types=1);

namespace Mush\Game\Enum;

final class EventPriorityEnum
{
    public const LOWEST = -1000;
    public const VERY_LOW = -100;
    public const LOW = -10;
    public const NORMAL = 0;
    public const HIGH = 10;
    public const VERY_HIGH = 100;
    public const HIGHEST = 1000;

    // those priority are for cycle changes
    public const DAEDALUS_VARIABLES = 12; // oxygen consumption
    public const PLAYERS = 10; // New points for players // effect of player statuses
    public const EQUIPMENTS = 8;  // recharge, effect of statuses
    public const DAEDALUS_INCIDENTS = 6; // trigger incident in the daedalus (falling ceiling electric arcs...)
    public const ROOMS = 4; // fires

    public const HUNTERS = 4; // hunter joining the party
    public const ATTRIBUTE_TITTLES = -2;
}
