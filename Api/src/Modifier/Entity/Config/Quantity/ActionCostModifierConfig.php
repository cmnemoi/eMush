<?php

namespace Mush\Modifier\Entity\Config\Quantity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Event\PlayerVariableEvent;

#[ORM\Entity]
class ActionCostModifierConfig extends QuantityModifierConfig
{

    private string $playerVariable;

    public function __construct(string $name, string $reach, int $quantity, string $mode, string $playerVariable,)
    {
        parent::__construct($name, $reach, $quantity, $mode);
        $this->playerVariable = $playerVariable;
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

        $value = match ($this->getMode()) {
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

    public function getPlayerVariable(): string
    {
        return $this->playerVariable;
    }

}
