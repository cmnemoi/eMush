<?php

declare(strict_types=1);

namespace Mush\Achievement\Dto;

use Mush\Achievement\Enum\StatisticEnum;

final readonly class StatisticConfigDto
{
    public function __construct(
        public StatisticEnum $name,
        public bool $isRare = false,
    ) {}
}
