<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class DailyReset extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_RESET;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        // Only applied on cycle 1
        if (!\in_array(EventEnum::NEW_DAY, $reasons, true) || $status->getCharge() >= $status->getThreshold()) {
            return $status;
        }
        $currentCharge = $status->getCharge();
        $finalCharge = $status->getThreshold() ?? 0;

        $this->statusService->updateCharge(
            $status,
            $finalCharge - $currentCharge,
            $reasons,
            $time
        );

        return $status;
    }
}
