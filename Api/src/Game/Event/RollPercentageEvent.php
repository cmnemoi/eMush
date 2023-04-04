<?php

namespace Mush\Game\Event;

use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;

class RollPercentageEvent extends AbstractGameEvent implements VariableEventInterface
{
    public const ROLL_PERCENTAGE = 'roll.percentage';

    protected GameVariable $percentageVariable;

    public function __construct(int $percentage, array $tags, \DateTime $time)
    {
        parent::__construct($tags, $time);

        if ($percentage > 0) {
            $minPercentage = 1;
        } else {
            $minPercentage = 0;
        }
        if ($percentage < 100) {
            $maxPercentage = 99;
        } else {
            $maxPercentage = 100;
        }

        $percentageVariable = new GameVariable(
            null,
            ActionVariableEnum::PERCENTAGE_SUCCESS,
            $percentage,
            $maxPercentage,
            $minPercentage
        );

        $this->percentageVariable = $percentageVariable;
    }

    public function getVariableName(): string
    {
        return ActionVariableEnum::PERCENTAGE_SUCCESS;
    }

    public function getVariable(): GameVariable
    {
        return $this->percentageVariable;
    }

    public function getQuantity(): int
    {
        return $this->percentageVariable->getValue();
    }

    public function setQuantity(int $quantity): self
    {
        $this->percentageVariable->setValue($quantity);

        return $this;
    }
}
