<?php

namespace Mush\Modifier\Entity\Quantity\ActionCost;

use Doctrine\ORM\Mapping as ORM;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\Quantity\QuantityModifier;

#[ORM\Entity]
class ActionCostModifier extends QuantityModifier
{

    private string $playerVariable;

    private array $targetActions = [];

    public function __construct(ModifierHolder $holder, string $name, int $quantity, string $playerVariable)
    {
        parent::__construct($holder, $name, $quantity);
        $this->playerVariable = $playerVariable;
    }

    public function addTargetActions(array $actionsNames) : void
    {
        $this->targetActions = array_merge($this->targetActions, $actionsNames);
    }

    public function addTargetAction(string $actionName) : void {
        $this->addTargetActions([$actionName]);
    }

    public function getTargetActions(): array
    {
        return $this->targetActions;
    }

    public function getPlayerVariable(): string
    {
        return $this->playerVariable;
    }

}
