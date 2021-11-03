<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\ModifierHolder;

interface AbstractQuantityEvent
{
    public const CHANGE_VARIABLE = 'change.variable';

    public function getQuantity(): int;

    public function setQuantity(int $quantity): self;

    public function getModifiedVariable(): string;

    public function getReason(): string;

    public function getModifierHolder(): ModifierHolder;
}
