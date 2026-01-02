<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

class CycleReset extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_RESET;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        if ($status->getCharge() >= $status->getThreshold()) {
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
