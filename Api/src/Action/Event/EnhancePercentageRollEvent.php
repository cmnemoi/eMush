<?php

namespace Mush\Action\Event;

use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;

class EnhancePercentageRollEvent extends PercentageRollEvent
{
    public const INJURY_ROLL_RATE = 'enhance_side_effect_roll_injury';
    public const CLUMSINESS_ROLL_RATE = 'enhance_side_effect_roll_clumsiness';
    public const DIRTY_ROLL_RATE = 'enhance_side_effect_roll_dirty';
    public const ACTION_ROLL_RATE = 'enhance_try_to_fail_roll_action';
    public const TRIGGER_ROLL_RATE = 'enhance_trigger_roll_rate';

    private ?ModifierConfig $modifier;
    private int $thresholdRate;
    private bool $tryToSucceed;

    public function __construct(ModifierHolder $modifierHolder, int $rate, int $thresholdRate, bool $tryToSucceed, string $reason, \DateTime $time)
    {
        parent::__construct($modifierHolder, $rate, $reason, $time);
        $this->thresholdRate = $thresholdRate;
        $this->tryToSucceed = $tryToSucceed;
        $this->modifier = null;
    }

    public function getThresholdRate(): int
    {
        return $this->thresholdRate;
    }

    public function tryToSucceed(): bool
    {
        return $this->tryToSucceed;
    }

    public function setThresholdRate(int $thresholdRate): void
    {
        $this->thresholdRate = $thresholdRate;
    }

    public function setModifierConfig(ModifierConfig $modifier): void
    {
        $this->modifier = $modifier;
    }

    public function getModifierConfig(): ?ModifierConfig
    {
        return $this->modifier;
    }
}
