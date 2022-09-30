<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractGameEvent;

class PrepareSideEffectRollEvent extends AbstractGameEvent
{

    private int $rate;

    public function __construct(int $baseRate, string $reason, \DateTime $time)
    {
        parent::__construct($reason, $time);
        $this->rate = $baseRate;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function addRate(int $rate) {
        $this->rate += $rate;
    }

    public function setRate(int $rate): void
    {
        $this->rate = $rate;
    }

}