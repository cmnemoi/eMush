<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlaceStatusEnum;

class CycleDecrement extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::CYCLE_DECREMENT;

    public function apply(ChargeStatus $status, array $reasons, \DateTime $time): ?ChargeStatus
    {
        return $this->statusService->updateCharge($status, -1, $reasons, $time, visibility: $this->getUpdateVisibility($status));
    }

    private function getUpdateVisibility(ChargeStatus $status): string
    {
        return match ($status->getName()) {
            PlaceStatusEnum::CEASEFIRE->value => VisibilityEnum::PUBLIC,
            default => VisibilityEnum::HIDDEN,
        };
    }
}
