<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyReset extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_RESET;

    public function apply(ChargeStatus $status, array $reasons): ?ChargeStatus
    {
        // Only applied on cycle 1
        if (!in_array(EventEnum::NEW_DAY, $reasons) || $status->getCharge() >= $status->getThreshold()) {
            return $status;
        }
        $status->setCharge($status->getThreshold() ?? 0);

        return $status;
    }
}
