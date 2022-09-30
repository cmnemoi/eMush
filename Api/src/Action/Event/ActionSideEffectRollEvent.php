<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Player\Entity\Player;

class ActionSideEffectRollEvent extends AbstractModifierHolderEvent
{

    public const INJURY_ROLL = 'side_effect_roll_injury';
    public const CLUMSINESS_ROLL_RATE = 'side_effect_roll_clumsiness';
    public const DIRTY_ROLL_RATE = 'side_effect_roll_dirty';

    private int $rate;

    public function __construct(Player $player, int $baseRate, string $reason, \DateTime $time)
    {
        parent::__construct($player, $reason, $time);
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