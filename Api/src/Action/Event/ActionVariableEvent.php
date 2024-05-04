<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionVariableEvent extends ActionEvent implements VariableEventInterface
{
    public const string APPLY_COST = 'apply.cost';
    public const string ROLL_ACTION_PERCENTAGE = 'roll.action.percentage';
    public const string GET_OUTPUT_QUANTITY = 'get.output.quantity';

    public const array VARIABLE_TO_EVENT_MAP = [
        PlayerVariableEnum::ACTION_POINT => self::APPLY_COST,
        PlayerVariableEnum::MORAL_POINT => self::APPLY_COST,
        PlayerVariableEnum::MOVEMENT_POINT => self::APPLY_COST,
        ActionVariableEnum::PERCENTAGE_SUCCESS => self::ROLL_ACTION_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_CRITICAL => self::ROLL_ACTION_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_DIRTINESS => self::ROLL_ACTION_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_INJURY => self::ROLL_ACTION_PERCENTAGE,
        ActionVariableEnum::OUTPUT_QUANTITY => self::GET_OUTPUT_QUANTITY,
    ];

    private float $quantity;
    private string $variableName;

    public function __construct(
        ActionConfig $actionConfig,
        ActionProviderInterface $actionProvider,
        string $variableName,
        float $quantity,
        Player $player,
        ?LogParameterInterface $actionTarget
    ) {
        $this->variableName = $variableName;
        $this->quantity = $quantity;

        parent::__construct(
            $actionConfig,
            $actionProvider,
            $player,
            $actionTarget,
        );
    }

    public function getRoundedQuantity(): int
    {
        return $this->getVariable()->getValueInRange((int) $this->quantity);
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function getVariable(): GameVariable
    {
        return $this->getActionConfig()->getVariableByName($this->variableName);
    }
}
