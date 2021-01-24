<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyDecrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_DECREMENT;

    public function apply(ChargeStatus $status, Daedalus $daedalus): void
    {
        //Only applied on cycle 1
        if ($daedalus->getCycle() !== 1 || $status->getCharge() <= $status->getThreshold()) {
            return;
        }

        $status->addCharge(-1);
    }
}
