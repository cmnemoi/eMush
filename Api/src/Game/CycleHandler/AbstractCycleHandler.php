<?php

namespace Mush\Game\CycleHandler;

use Mush\Equipment\Entity\GameEquipment;

abstract class AbstractCycleHandler
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleNewCycle(GameEquipment $gameEquipment, \DateTime $dateTime): void;

    abstract public function handleNewDay(GameEquipment $gameEquipment, \DateTime $dateTime): void;
}
