<?php

declare(strict_types=1);

namespace Mush\Achievement\Enum;

enum AchievementEnum: string
{
    case GAGGED_1 = 'gagged_1';
    case PLANET_SCANNED_1 = 'planet_scanned_1';
    case SIGNAL_EQUIP_1 = 'signal_equip_1';
    case SIGNAL_EQUIP_20 = 'signal_equip_20';
    case SIGNAL_EQUIP_50 = 'signal_equip_50';
    case SIGNAL_EQUIP_200 = 'signal_equip_200';
    case SIGNAL_EQUIP_1000 = 'signal_equip_1000';
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
