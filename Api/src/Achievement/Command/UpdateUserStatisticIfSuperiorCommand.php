<?php

declare(strict_types=1);

namespace Mush\Achievement\Command;

use Mush\Achievement\Enum\StatisticEnum;

final readonly class UpdateUserStatisticIfSuperiorCommand
{
    public function __construct(
        public int $userId,
        public StatisticEnum $statisticName,
        public string $language,
        public int $newValue,
    ) {}
}
