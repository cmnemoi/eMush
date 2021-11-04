<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class CycleIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_INCREMENT;

    public function apply(ChargeStatus $status, string $reason): ?ChargeStatus
    {
        return $this->statusService->updateCharge($status, 1);
    }
}
