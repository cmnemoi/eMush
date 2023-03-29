<?php

namespace Mush\Action\Event;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Game\Entity\GameVariable;
use Mush\Game\Entity\GameVariableHolderInterface;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionVariableEvent extends ActionEvent implements VariableEventInterface
{
    public const APPLY_COST = 'apply.cost';
    public const MOVEMENT_CONVERSION = 'movement.conversion';
    public const ROLL_PERCENTAGE_SUCCESS = 'roll_percentage_success';
    public const ROLL_PERCENTAGE_DIRTY = 'roll_percentage_dirty';
    public const ROLL_PERCENTAGE_INJURY = 'roll_percentage_injury';

    public const VARIABLE_TO_EVENT_MAP = [
        PlayerVariableEnum::ACTION_POINT => self::APPLY_COST,
        PlayerVariableEnum::MORAL_POINT => self::APPLY_COST,
        PlayerVariableEnum::MOVEMENT_POINT => self::APPLY_COST,
        ActionVariableEnum::PERCENTAGE_SUCCESS => self::ROLL_PERCENTAGE_SUCCESS,
        ActionVariableEnum::PERCENTAGE_CRITICAL => self::ROLL_PERCENTAGE_SUCCESS,
        ActionVariableEnum::PERCENTAGE_DIRTINESS => self::ROLL_PERCENTAGE_DIRTY,
        ActionVariableEnum::PERCENTAGE_INJURY => self::ROLL_PERCENTAGE_INJURY,
    ];


    private int $quantity;
    private string $variableName;

    public function __construct(
        Action $action,
        string $variableName,
        int $quantity,
        Player $player,
        ?LogParameterInterface $actionParameter
    )
    {
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
        return $this->getPlayer()->getVariableByName($this->variableName);
    }

    public function getVariableHolder(): GameVariableHolderInterface
    {
        return $this->getPlayer();
    }

    public function getModifierHolder(): ModifierHolder
    {
        return $this->getPlayer();
    }
}
