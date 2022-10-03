<?php

namespace Mush\Modifier\Event;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Modifier;

class ModifierEvent extends AbstractGameEvent
{
    public const APPLY_MODIFIER = 'apply_modifier';

    private Modifier $modifier;

    public function __construct(
        Modifier $modifier,
        string $reason,
        \DateTime $time,
    ) {
        parent::__construct($reason, $time);

        $this->modifier = $modifier;
    }

    public function getModifier(): Modifier
    {
        return $this->modifier;
    }
}
