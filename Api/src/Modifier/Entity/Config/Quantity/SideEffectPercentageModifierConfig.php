<?php

namespace Mush\Modifier\Entity\Config\Quantity;

use Mush\Action\Event\PrepareSideEffectRollEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierModeEnum;

class SideEffectPercentageModifierConfig extends QuantityModifierConfig {

    public function __construct(string $name, string $reach, int $quantity, string $mode)
    {
        parent::__construct($name, $reach, $quantity, $mode);
    }

    public function modify(AbstractGameEvent $event)
    {
        if ($event instanceof PrepareSideEffectRollEvent) {
            switch ($this->getMode()) {
                case ModifierModeEnum::SET_VALUE:
                    $event->setRate($this->getQuantity());
                    break;

                case ModifierModeEnum::MULTIPLICATIVE:
                    $event->setRate($event->getRate() * $this->getQuantity());
                    break;

                case ModifierModeEnum::ADDITIVE:
                    $event->addRate($this->getQuantity());
                    break;
            }
        }
    }

}