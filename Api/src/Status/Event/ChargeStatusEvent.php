<?php

namespace Mush\Status\Event;

use Mush\Status\Enum\ChargeStrategyTypeEnum;

class ChargeStatusEvent extends StatusEvent
{
    private int $threshold = 0;
    private int $startCharge = 0;
    private string $dischargeStrategy = ChargeStrategyTypeEnum::NONE;

    public function setThreshold(int $threshold): ChargeStatusEvent
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function setInitCharge(int $startCharge): ChargeStatusEvent
    {
        $this->startCharge = $startCharge;

        return $this;
    }

    public function getInitCharge(): int
    {
        return $this->startCharge;
    }

    public function setDischargeStrategy(string $dischargeStrategy): ChargeStatusEvent
    {
        $this->dischargeStrategy = $dischargeStrategy;

        return $this;
    }

    public function getDischargeStrategy(): string
    {
        return $this->dischargeStrategy;
    }
}
