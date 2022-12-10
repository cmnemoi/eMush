<?php

namespace Mush\Game\CycleHandler;

abstract class AbstractCycleHandler
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleNewCycle(\Mush\Equipment\Entity\GameEquipment $object, \DateTime $dateTime): void;

    abstract public function handleNewDay(\Mush\Equipment\Entity\GameEquipment $object, \DateTime $dateTime): void;
}
