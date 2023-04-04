<?php

namespace Mush\Game\Event;

use Mush\Game\Entity\GameVariable;

interface VariableEventInterface
{
    public const CHANGE_VARIABLE = 'change.variable';
    public const CHANGE_VALUE_MAX = 'change.value.max';
    public const SET_VALUE = 'set.value';
    public const ROLL_PERCENTAGE = 'roll.percentage';

    public function getVariableName(): string;

    public function getVariable(): GameVariable;

    public function getQuantity(): int;

    public function setQuantity(int $quantity): self;

    public function getTags(): array;

    public function getTime(): \DateTime;
}
