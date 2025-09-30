<?php

declare(strict_types=1);

namespace Mush\Achievement\ViewModel;

final readonly class AchievementViewModel
{
    public function __construct(
        public string $key,
        public int $points,
        public int $threshold,
        public string $statisticKey,
        public bool $isRare,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            key: $row['key'],
            points: $row['points'],
            threshold: $row['threshold'],
            statisticKey: $row['statistic_key'],
            isRare: $row['is_rare'],
        );
    }
}
