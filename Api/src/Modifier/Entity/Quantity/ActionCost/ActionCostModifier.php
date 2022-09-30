<?php

namespace Mush\Modifier\Entity\Quantity\ActionCost;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\Quantity\QuantityModifier;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Event\PlayerVariableEvent;

#[ORM\Entity]
class ActionCostModifier extends QuantityModifier
{

    private string $playerVariable;

    private string $mode;

    public function __construct(ModifierHolder $holder, string $name, int $quantity, string $playerVariable, string $mode)
    {
        parent::__construct($holder, $name, $quantity);
        $this->playerVariable = $playerVariable;
        $this->mode = $mode;
    }

    public function modify(AbstractGameEvent $event)
    {
        if (!$event instanceof PlayerVariableEvent) {
            return;
        }

        if ($event->getModifiedVariable() !== $this->playerVariable) {
            return;
        }

        $actualQuantity = $event->getQuantity();
        $modifierQuantity = $this->getQuantity();

        switch ($this->mode)
        {
            case ModifierModeEnum::ADDITIVE:
                $event->setQuantity($actualQuantity + $modifierQuantity);
                break;
            case ModifierModeEnum::MULTIPLICATIVE:
                $event->setQuantity($actualQuantity * $modifierQuantity);
                break;
            case ModifierModeEnum::SET_VALUE:
                $event->setQuantity($modifierQuantity);
                break;
        }
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getPlayerVariable(): string
    {
        return $this->playerVariable;
    }

}
