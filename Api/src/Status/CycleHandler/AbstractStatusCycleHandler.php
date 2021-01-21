<?php

namespace Mush\Status\CycleHandler;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

abstract class AbstractStatusCycleHandler
{
    protected string $name = '';

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleNewCycle(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void;

    abstract public function handleNewDay(Status $status, Daedalus $daedalus, StatusHolderInterface $statusHolder, \DateTime $dateTime): void;
}
