<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_INCREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        // Only applied on cycle 1
        if (!\in_array(EventEnum::NEW_DAY, $reasons, true)) {
            return $status;
        }

        return $this->statusService->updateCharge($status, 1, $reasons, $time);
    }
}
