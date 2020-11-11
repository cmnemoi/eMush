<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\Status;
use Status\Enum\ChargeStrategyTypeEnum;

class CycleDecrease
{
    public function apply(Status $status)
    {
        if (
            ChargeStrategyTypeEnum::CYCLE_DECREMENT !== $status->getStrategy() ||
            $status->getCharge() <= $status->getThreshold()
        ) {
            return;
        }
        $status->addCharge(-1);
    }
}
