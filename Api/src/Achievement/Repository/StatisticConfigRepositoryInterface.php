<?php

declare(strict_types=1);

namespace Mush\Achievement\Repository;

use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;

interface StatisticConfigRepositoryInterface
{
    public function findOneByName(StatisticEnum $name): StatisticConfig;
}
