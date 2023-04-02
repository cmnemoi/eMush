<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionVariableEvent extends ActionEvent implements VariableEventInterface
{
    public const APPLY_COST = 'apply.cost';
    public const MOVEMENT_CONVERSION = 'movement.conversion';

    public const VARIABLE_TO_EVENT_MAP = [
        PlayerVariableEnum::ACTION_POINT => self::APPLY_COST,
        PlayerVariableEnum::MORAL_POINT => self::APPLY_COST,
        PlayerVariableEnum::MOVEMENT_POINT => self::APPLY_COST,
        ActionVariableEnum::PERCENTAGE_SUCCESS => VariableEventInterface::ROLL_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_CRITICAL => VariableEventInterface::ROLL_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_DIRTINESS => VariableEventInterface::ROLL_PERCENTAGE,
        ActionVariableEnum::PERCENTAGE_INJURY => VariableEventInterface::ROLL_PERCENTAGE,
    ];

    private int $quantity;
    private string $variableName;

    public function __construct(
        Action $action,
        string $variableName,
        int $quantity,
        Player $player,
        ?LogParameterInterface $actionParameter
    ) {
        $this->variableName = $variableName;
        $this->quantity = $quantity;

        parent::__construct(
            $action,
            $player,
            $actionParameter
        );
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
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
        return $this->getAction()->getVariableByName($this->variableName);
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->getAction();
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->getAuthor();
    }
}
