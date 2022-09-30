<?php

namespace Mush\Modifier\Entity\Config\Quantity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Event\PlayerVariableEvent;

#[ORM\Entity]
class PlayerVariableModifierConfig extends QuantityModifierConfig
{

    private string $playerVariable;

    public function __construct(string $name, string $reach, int $quantity, string $mode, string $playerVariable)
    {
        parent::__construct($name, $reach, $quantity, $mode);
        $this->playerVariable = $playerVariable;
    }

    public function modify(AbstractModifierHolderEvent $event, EventServiceInterface $eventService)
    {
        if ($event instanceof PlayerVariableEvent) {
            if ($this->playerVariable !== $event->getModifiedVariable()) {
                return;
            }

            $modifierQuantity = $this->getQuantity();
            $eventQuantity = $event->getQuantity();

            switch ($this->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    $event->setQuantity($modifierQuantity);
                    break;

                case ModifierModeEnum::MULTIPLICATIVE:
                    if ($this->canProceed($eventQuantity, $modifierQuantity)) {
                        $event->setQuantity($eventQuantity * $modifierQuantity);
                    }
                    break;

                case ModifierModeEnum::ADDITIVE:
                    if ($this->canProceed($eventQuantity, $modifierQuantity)) {
                        $event->setQuantity($eventQuantity + $modifierQuantity);
                    }
                    break;
            }
        }
    }

    private function canProceed(int $quantity1, int $quantity2) : bool {
        return ($quantity1 > 0 && $quantity2 < 0) || ($quantity2 > 0 && $quantity1 < 0);
    }
}