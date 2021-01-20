<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyDecrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_DECREMENT;

    public function apply(ChargeStatus $status): void
    {
        $daedalus = $this->statusService->getDaedalus($status);

        //Only applied on cycle 1
        if ($daedalus->getCycle() !== 1 || $status->getCharge() <= $status->getThreshold()) {
            return;
        }

        $status->addCharge(-1);
    }
}
