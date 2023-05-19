<?php

namespace Mush\Disease\SymptomHandler;

abstract class AbstractSymptomHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function applyEffects(string $symptomName, AbstractGameEvent $triggeringEvent, \DateTime $time): void;
}
