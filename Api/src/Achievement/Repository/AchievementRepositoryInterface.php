<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\Achievement;

interface AchievementRepositoryInterface
{
    public function existsForStatistic(int $statisticId): bool;

    public function save(Achievement $achievement): void;
}
