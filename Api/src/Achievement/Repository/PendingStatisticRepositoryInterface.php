<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\PendingStatistic;
use Mush\Achievement\Enum\StatisticEnum;

interface PendingStatisticRepositoryInterface
{
    /**
     * @return PendingStatistic[]
     */
    public function findAllByClosedDaedalusId(int $closedDaedalusId): array;

    public function findByNameUserIdAndClosedDaedalusIdOrNull(StatisticEnum $name, int $userId, int $closedDaedalusId): ?PendingStatistic;

    public function findOrCreateByNameUserIdAndClosedDaedalusId(StatisticEnum $name, int $userId, int $closedDaedalusId): PendingStatistic;

    public function findOneById(int $id): PendingStatistic;

    public function save(PendingStatistic $statistic): void;

    public function delete(PendingStatistic $statistic): void;
}
