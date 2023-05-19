<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Game\Event\AbstractGameEvent;
use Mush\Player\Entity\Player;

abstract class AbstractSymptomHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function applyEffects(string $symptomName, AbstractGameEvent $triggeringEvent, \DateTime $time): void;
}
