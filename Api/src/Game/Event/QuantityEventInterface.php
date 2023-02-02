<?php

namespace Mush\Game\Event;

use Mush\Modifier\Entity\ModifierHolder;

interface QuantityEventInterface
{
    public const CHANGE_VARIABLE = 'change.variable';

    public function getQuantity(): int;

    public function setQuantity(int $quantity): self;

    public function getModifiedVariable(): string;

    public function getTags(): array;

    public function getModifierHolder(): ModifierHolder;

    public function getTime(): \DateTime;
}
