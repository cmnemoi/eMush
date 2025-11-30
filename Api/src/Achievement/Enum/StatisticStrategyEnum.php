<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum StatisticStrategyEnum: string
{
    case MAX = 'max';
    case INCREMENT = 'increment';
    case NULL = '';
}
