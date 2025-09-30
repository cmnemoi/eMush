<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum AchievementEnum: string
{
    case PLANET_SCANNED_1 = 'planet_scanned_1';
    case NULL = '';

    public function toStatisticName(): StatisticEnum
    {
        $statisticName = preg_replace('/_\d+$/', '', $this->value);
        if (!$statisticName) {
            throw new \Exception("Could not parse statistic name from {$this->value}");
        }

        return StatisticEnum::from($statisticName);
    }
}
