<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Achievement\TestDoubles;

use Mush\Achievement\ConfigData\StatisticConfigData;
use Mush\Achievement\Entity\StatisticConfig;
use Mush\Achievement\Enum\StatisticEnum;
use Mush\Achievement\Repository\StatisticConfigRepositoryInterface;

final class InMemoryStatisticConfigRepository implements StatisticConfigRepositoryInterface
{
    public function findOneByName(StatisticEnum $name): StatisticConfig
    {
        return StatisticConfig::fromDto(StatisticConfigData::getByName($name));
    }
}
