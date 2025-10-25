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
            new StatisticConfigDto(StatisticEnum::CAT_CUDDLED),
            new StatisticConfigDto(StatisticEnum::EXTINGUISH_FIRE),
            new StatisticConfigDto(StatisticEnum::GAGGED),
            new StatisticConfigDto(StatisticEnum::PLANET_SCANNED),
            new StatisticConfigDto(StatisticEnum::SIGNAL_FIRE),
            new StatisticConfigDto(StatisticEnum::SIGNAL_EQUIP),
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
