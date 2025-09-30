<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Achievement\TestDoubles;

use Mush\Achievement\Entity\AchievementConfig;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Repository\AchievementConfigRepositoryInterface;

final class InMemoryAchievementConfigRepository implements AchievementConfigRepositoryInterface
{
    /** @var array<string, AchievementConfig> */
    private array $achievementConfigs = [];

    public function findAllToUnlockForStatistic(Statistic $statistic): array
    {
        return array_filter(
            $this->achievementConfigs,
            static fn (AchievementConfig $achievementConfig) => $achievementConfig->shouldUnlockAchievementForStatistic($statistic)
        );
    }

    public function save(AchievementConfig $achievementConfig): void
    {
        $this->achievementConfigs[$achievementConfig->getName()->value] = $achievementConfig;
    }
}
