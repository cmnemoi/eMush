<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\Statistic;
use Mush\Achievement\Enum\StatisticEnum;

interface StatisticRepositoryInterface
{
    public function findByNameAndUserIdOrNull(StatisticEnum $name, int $userId): ?Statistic;

    public function findOneById(int $id): Statistic;

    public function save(Statistic $statistic): void;
}
