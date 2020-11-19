<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\Status;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class PlantStrategy
{
    public function apply(Status $status)
    {
        if (
            ChargeStrategyTypeEnum::PLANT !== $status->getStrategy() ||
            $status->getCharge() >= $status->getThreshold()
        ) {
            return;
        }

        //@TODO: Handle garden
        $status->addCharge(1);
    }
}
