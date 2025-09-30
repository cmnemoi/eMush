<?php

declare(strict_types=1);

namespace Mush\Achievement\ViewModel;

final readonly class StatisticViewModel
{
    public function __construct(
        public string $key,
        public int $count,
        public bool $isRare,
    ) {}

    public static function fromQueryRow(array $queryResult): self
    {
        return new self(
            key: $queryResult['name'],
            count: $queryResult['count'],
            isRare: $queryResult['is_rare'],
        );
    }
}
