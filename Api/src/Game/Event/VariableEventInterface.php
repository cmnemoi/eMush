<?php

namespace Mush\Game\Event;

use Mush\Game\Entity\GameVariable;

interface VariableEventInterface
{
    public const CHANGE_VARIABLE = 'change.variable';
    public const CHANGE_VALUE_MAX = 'change.value.max';
    public const SET_VALUE = 'set.value';
    public const ROLL_PERCENTAGE = 'roll.percentage';

    public const GAIN = 'variable.gain';
    public const LOSS = 'variable.loss';

    public function getVariableName(): string;

    public function getVariable(): GameVariable;

    public function getRoundedQuantity(): int;

    public function getQuantity(): float;

    public function setQuantity(float $quantity): self;

    public function getTags(): array;

    public function getTime(): \DateTime;
}
