<?php

namespace Mush\Modifier\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Modifier;

class ModifierEvent extends AbstractGameEvent
{
    public const APPLY_MODIFIER = 'apply_modifier';

    protected Modifier $modifier;
    protected bool $wasModifierUsed;

    public function __construct(
        Modifier $modifier,
        string $reason,
        \DateTime $time,
        bool $wasModifierUsed
    ) {
        parent::__construct($reason, $time);

        $this->modifier = $modifier;
        $this->wasModifierUsed = $wasModifierUsed;
    }

    public function getModifier(): Modifier
    {
        return $this->modifier;
    }

    public function wasModifierUsed(): bool
    {
        return $this->wasModifierUsed;
    }
}
