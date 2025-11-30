<?php

declare(strict_types=1);

namespace Mush\Achievement\Dto;

use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Enum\StatisticStrategyEnum;

final readonly class StatisticConfigDto
{
    public function __construct(
        public StatisticEnum $name,
        public StatisticStrategyEnum $strategy,
        public bool $isRare = false,
    ) {}
}
