<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\EventEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;

final class SkillPointsIncrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::SKILL_POINTS_INCREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        // Only applied on cycle 1
        if (!\in_array(EventEnum::NEW_DAY, $reasons, true)) {
            return $status;
        }

        return $this->statusService->updateCharge($status, (int) ($status->getThreshold() / 2), $reasons, $time);
    }
}
