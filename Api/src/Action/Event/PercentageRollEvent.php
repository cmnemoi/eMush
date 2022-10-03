<?php

namespace Mush\Action\Event;

use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;

class PercentageRollEvent extends AbstractModifierHolderEvent
{

    public const INJURY_ROLL_RATE = 'side_effect_roll_injury';
    public const CLUMSINESS_ROLL_RATE = 'side_effect_roll_clumsiness';
    public const DIRTY_ROLL_RATE = 'side_effect_roll_dirty';
    public const ACTION_ROLL_RATE = 'roll_action';

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