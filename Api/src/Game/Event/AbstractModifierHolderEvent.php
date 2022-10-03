<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\ModifierHolder;

abstract class AbstractModifierHolderEvent extends AbstractGameEvent
{
    private ModifierHolder $modifierHolder;

    public function __construct(ModifierHolder $modifierHolder, string $reason, \DateTime $time)
    {
        parent::__construct($reason, $time);
        $this->modifierHolder = $modifierHolder;
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->modifierHolder;
    }
}
