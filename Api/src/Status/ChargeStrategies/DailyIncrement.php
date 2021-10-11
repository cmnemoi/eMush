<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_INCREMENT;

    public function apply(ChargeStatus $status, string $reason): ?ChargeStatus
    {
        //Only applied on cycle 1
        if ($reason !== EventEnum::NEW_DAY) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1);
    }
}
