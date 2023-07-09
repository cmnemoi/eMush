<?php

namespace Mush\Modifier\ModifierHandler;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

abstract class AbstractModifierHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleEventModifier(EventModifierConfig $modifierConfig, AbstractGameEvent $event): void;
}
