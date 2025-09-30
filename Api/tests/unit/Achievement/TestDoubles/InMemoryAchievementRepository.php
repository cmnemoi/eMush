<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Achievement\TestDoubles;

use Mush\Achievement\Entity\Achievement;
use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\AchievementEnum;
use Mush\Achievement\Repository\AchievementRepositoryInterface;

final class InMemoryAchievementRepository implements AchievementRepositoryInterface
{
    /** @var array<string, Achievement> */
    private array $achievements = [];

    public function existsForStatistic(int $statisticId): bool
    {
        foreach ($this->achievements as $achievement) {
            if ($achievement->getStatisticId() === $statisticId) {
                return true;
            }
        }

        return false;
    }

    public function save(Achievement $achievement): void
    {
        foreach ($this->achievements as $key => $value) {
            if ($value->getName() === $achievement->getName()) {
                $this->achievements[$key] = $achievement;
            }
        }

        $this->achievements[] = $this->copy($achievement);
    }

    public function findOneByNameOrNull(AchievementEnum $name): ?Achievement
    {
        foreach ($this->achievements as $achievement) {
            if ($achievement->getName() === $name) {
                return $this->copy($achievement);
            }
        }

        return null;
    }

    public function findAllByStatistic(Statistic $statistic): array
    {
        return array_filter(
            $this->achievements,
            static fn (Achievement $achievement) => $achievement->getStatisticId() === $statistic->getId()
        );
    }

    private function copy(Achievement $achievement): Achievement
    {
        return new Achievement(
            $achievement->getConfig(),
            $achievement->getStatisticId()
        );
    }
}
