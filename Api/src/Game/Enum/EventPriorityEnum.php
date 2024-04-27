<?php

declare(strict_types=1);

namespace Mush\Game\Enum;

abstract class EventPriorityEnum
{
    public const int LOWEST = -1000;
    public const int VERY_LOW = -100;
    public const int LOW = -10;
    public const int NORMAL = 0;
    public const int HIGH = 10;
    public const int VERY_HIGH = 100;
    public const int HIGHEST = 1000;

    // those priority are for cycle changes
    public const int DAEDALUS_VARIABLES = 12; // oxygen consumption
    public const int PLAYERS = 10; // New points for players // effect of player statuses
    public const int EQUIPMENTS = 8;  // recharge, effect of statuses
    public const int DAEDALUS_INCIDENTS = 6; // trigger incident in the daedalus (falling ceiling electric arcs...)
    public const int ROOMS = 4; // fires

    public const int HUNTERS = 4; // hunter joining the party
    public const int ATTRIBUTE_TITTLES = -2;
}
