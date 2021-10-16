<?php

namespace Mush\Status\Event;

class ChargeStatusEvent extends StatusEvent
{
    private int $threshold = 0;
    private int $startCharge = 0;

    public function setThreshold(int $threshold): StatusEvent
    {
        $this->threshold = $threshold;

        return $this;
    }

    public function getThreshold(): int
    {
        return $this->threshold;
    }

    public function setInitCharge(int $startCharge): StatusEvent
    {
        $this->startCharge = $startCharge;

        return $this;
    }

    public function getInitCharge(): int
    {
        return $this->startCharge;
    }
}
