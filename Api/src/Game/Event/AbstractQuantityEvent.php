<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\ModifierHolder;

interface AbstractQuantityEvent
{
    public function getQuantity(): int;

    public function setQuantity(int $quantity): self;

    public function getReason(): string;

    public function getModifierHolder(): ModifierHolder;
}
