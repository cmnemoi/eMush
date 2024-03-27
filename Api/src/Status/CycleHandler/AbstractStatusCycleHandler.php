<?php

namespace Mush\Status\CycleHandler;

use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;

abstract readonly class AbstractStatusCycleHandler
{
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function handleNewCycle(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void;

    abstract public function handleNewDay(Status $status, StatusHolderInterface $statusHolder, \DateTime $dateTime): void;
}
