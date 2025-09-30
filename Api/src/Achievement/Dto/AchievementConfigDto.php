<?php

declare(strict_types=1);

namespace Mush\Achievement\Dto;

use Mush\Achievement\Enum\AchievementEnum;
use Mush\Achievement\Enum\StatisticEnum;

final readonly class AchievementConfigDto
{
    public StatisticEnum $statistic;

    public function __construct(
        public AchievementEnum $name,
        public int $points,
        public int $threshold,
    ) {
        $this->statistic = $name->toStatisticName();
    }
}
