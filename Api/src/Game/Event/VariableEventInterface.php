<?php

namespace Mush\Game\Event;

use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Modifier\Entity\ModifierHolder;

interface VariableEventInterface
{
    public const CHANGE_VARIABLE = 'change.variable';
    public const CHANGE_VALUE_MAX = 'change.value.max';
    public const SET_VALUE = 'set.value';

    public function getVariableName(): string;

    public function getVariable(): GameVariable;

    public function getQuantity(): int;

    public function setQuantity(int $quantity): self;

    public function getVariableHolder(): GameVariableHolderInterface;

    public function getTags(): array;

    public function getTime(): \DateTime;

    public function getModifierHolder(): ModifierHolder;
}
