<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Entity\AchievementConfig;

interface AchievementRepositoryInterface
{
    public function existsForStatisticAndConfig(int $statisticId, AchievementConfig $config): bool;

    public function save(Achievement $achievement): void;
}
