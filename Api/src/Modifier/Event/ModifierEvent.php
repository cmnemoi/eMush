<?php

namespace Mush\Modifier\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\GameModifier;

class ModifierEvent extends AbstractGameEvent
{
    public const APPLY_MODIFIER = 'apply_modifier';

    protected GameModifier $modifier;
    protected bool $wasModifierUsed;

    public function __construct(
        GameModifier $modifier,
        string $reason,
        \DateTime $time,
        bool $wasModifierUsed
    ) {
        parent::__construct($reason, $time);

        $this->modifier = $modifier;
        $this->wasModifierUsed = $wasModifierUsed;
    }

    public function getModifier(): GameModifier
    {
        return $this->modifier;
    }

    public function wasModifierUsed(): bool
    {
        return $this->wasModifierUsed;
    }
}
