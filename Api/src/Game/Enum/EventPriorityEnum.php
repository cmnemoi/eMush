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
}
