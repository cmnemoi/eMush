<?php

namespace Mush\Game\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;

abstract class AbstractCycleHandler
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleNewCycle($object, Daedalus $daedalus, \DateTime $dateTime);

    abstract public function handleNewDay($object, Daedalus $daedalus, \DateTime $dateTime);
}
