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

        $value = match ($this->mode) {
            ModifierModeEnum::ADDITIVE => $actualQuantity + $modifierQuantity,
            ModifierModeEnum::MULTIPLICATIVE => $actualQuantity * $modifierQuantity,
            ModifierModeEnum::SET_VALUE => $modifierQuantity,
            default => throw new \LogicException('The Modifier Mode is not correct.'),
        };

        if (($value < 0 && $actualQuantity > 0) || ($value > 0 && $actualQuantity < 0)) {
            $event->setQuantity(0);
        } else {
            $event->setQuantity($value);
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
