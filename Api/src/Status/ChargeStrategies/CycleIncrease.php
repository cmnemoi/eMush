<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\Status;
use Status\Enum\ChargeStrategyTypeEnum;

class CycleIncrease
{
    public function apply(Status $status) {
        if ($status->getStrategy() !== ChargeStrategyTypeEnum::CYCLE_INCREMENT ||
            $status->getCharge() >= $status->getThreshold()
        ) {
            return;
        }
        $status->addCharge(1);
    }
}