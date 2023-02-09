<?php

namespace Mush\Game\Event;

use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Modifier\Entity\ModifierHolder;

interface VariableEventInterface
{
    public const CHANGE_VARIABLE = 'change.variable';
    public const GET_MAX_VALUE = 'get.max.value';
    public const SET_VALUE_MAX = 'set.value.max';
    public const SET_VALUE_MIN = 'set.value.min';
    public const SET_VALUE = 'set.value';
    public const ROLL_PERCENTAGE = 'roll.percentage';

    public function getVariableName(): string;

    public function getVariable(): GameVariable;

    public function getQuantity(): ?int;

    public function setQuantity(int $quantity): self;

    public function getVariableHolder(): GameVariableHolderInterface;
}
