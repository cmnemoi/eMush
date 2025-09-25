<?php

declare(strict_types=1);

namespace Mush\Daedalus\ViewModel;

use Mush\Game\ViewModel\ViewModelInterface;

final readonly class RankingDaedalusViewModel implements ViewModelInterface
{
    public function __construct(
        public int $daedalusId,
        public string $endCause,
        public int $daysSurvived,
        public int $cyclesSurvived,
        public int $humanTriumphSum,
        public int $mushTriumphSum,
        public int $highestHumanTriumph,
        public int $highestMushTriumph,
        public string $daedalusLanguage,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['end_cause'],
            (int) $row['days_survived'],
            (int) $row['cycles_survived'],
            (int) $row['human_triumph_sum'],
            (int) $row['mush_triumph_sum'],
            (int) $row['highest_human_triumph'],
            (int) $row['highest_mush_triumph'],
            (string) $row['language'],
        );
    }
}
