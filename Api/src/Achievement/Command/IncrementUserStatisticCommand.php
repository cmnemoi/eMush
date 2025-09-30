<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Enum\StatisticEnum;

final readonly class IncrementUserStatisticCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly StatisticEnum $statisticName,
        public readonly string $language,
    ) {}
}
