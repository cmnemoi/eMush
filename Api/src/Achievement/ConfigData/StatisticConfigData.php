<?php

declare(strict_types=1);

namespace Mush\Achievement\ConfigData;

use Mush\Achievement\Dto\StatisticConfigDto;
use Mush\Achievement\Enum\StatisticEnum;

/** @codeCoverageIgnore */
abstract class StatisticConfigData
{
    /** @return StatisticConfigDto[] */
    public static function getAll(): array
    {
        return [
            new StatisticConfigDto(
                name: StatisticEnum::PLANET_SCANNED,
            ),
        ];
    }

    public static function getByName(StatisticEnum $name): StatisticConfigDto
    {
        if ($name === StatisticEnum::NULL) {
            return new StatisticConfigDto(StatisticEnum::NULL, false);
        }

        $statistics = array_filter(self::getAll(), static fn (StatisticConfigDto $dto) => $dto->name === $name);
        $statistic = current($statistics);

        if (!$statistic) {
            throw new \Exception("Statistic config {$name->value} not found!");
        }

        return $statistic;
    }
}
