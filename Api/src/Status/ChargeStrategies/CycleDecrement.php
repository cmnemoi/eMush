<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class CycleDecrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_DECREMENT;

    public function apply(ChargeStatus $status, array $reasons): ?ChargeStatus
    {
        return $this->statusService->updateCharge($status, -1);
    }
}
