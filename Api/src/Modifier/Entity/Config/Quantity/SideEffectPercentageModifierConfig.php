<?php

namespace Mush\Modifier\Entity\Config\Quantity;

use Mush\Action\Enum\ActionSideEffectEventEnum;
use Mush\Action\Event\ActionSideEffectRollEvent;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Game\Event\AbstractModifierHolderEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Enum\ModifierModeEnum;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SideEffectPercentageModifierConfig extends QuantityModifierConfig {

    public function __construct(string $name, string $reach, int $quantity, string $mode)
    {
        parent::__construct($name, $reach, $quantity, $mode);
    }

    public function modify(AbstractModifierHolderEvent $event, EventServiceInterface $eventService)
    {
        if ($event instanceof ActionSideEffectRollEvent) {
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