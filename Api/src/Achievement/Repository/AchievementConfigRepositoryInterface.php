<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\Statistic;

interface AchievementConfigRepositoryInterface
{
    public function findAllToUnlockForStatistic(Statistic $statistic): array;

    public function save(AchievementConfig $achievementConfig): void;
}
