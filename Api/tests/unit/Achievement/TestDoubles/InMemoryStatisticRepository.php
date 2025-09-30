<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Achievement\TestDoubles;

use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticRepositoryInterface;

final class InMemoryStatisticRepository implements StatisticRepositoryInterface
{
    /** @var array<string, Statistic> */
    private array $statistics = [];

    public function findByNameAndUserIdOrNull(StatisticEnum $name, int $userId): ?Statistic
    {
        return $this->statistics[$name->value . ':' . $userId] ?? null;
    }

    public function findOneById(int $id): Statistic
    {
        foreach ($this->statistics as $statistic) {
            if ($statistic->getId() === $id) {
                return $statistic;
            }
        }

        throw new \Exception('Statistic not found');
    }

    public function save(Statistic $statistic): void
    {
        new \ReflectionProperty($statistic, 'id')->setValue($statistic, crc32(serialize($statistic)));
        $this->statistics[$statistic->toArray()['name']->value . ':' . $statistic->getUserId()] = $statistic;
    }
}
