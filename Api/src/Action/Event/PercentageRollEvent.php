<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Modifier\Entity\ModifierHolder;

class PercentageRollEvent extends AbstractModifierHolderEvent
{
    private int $rate;

    public function __construct(ModifierHolder $modifierHolder, int $baseRate, string $reason, \DateTime $time)
    {
        parent::__construct($modifierHolder, $reason, $time);
        $this->rate = $baseRate;
    }

    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(int $rate): void
    {
        $this->rate = $rate;
    }
}
