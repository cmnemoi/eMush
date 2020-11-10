<?php


namespace Mush\Status\ChargeStrategies;


use Mush\Status\Entity\Status;
use Status\Enum\ChargeStrategyTypeEnum;

class PlantStrategy
{
    public function apply(Status $status) {
        if ($status->getStrategy() !== ChargeStrategyTypeEnum::PLANT ||
            $status->getCharge() >= $status->getThreshold()
        ) {
            return;
        }

        //@TODO: Handle garden
        $status->addCharge(1);
    }
}