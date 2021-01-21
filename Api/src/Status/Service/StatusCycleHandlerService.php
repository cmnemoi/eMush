<?php

namespace Mush\Status\Service;

use Mush\Status\CycleHandler\AbstractStatusCycleHandler;
use Mush\Status\Entity\Status;

class StatusCycleHandlerService implements StatusCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractStatusCycleHandler $cycleHandler): void
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getStatusCycleHandler(Status $status): ?AbstractStatusCycleHandler
    {
        if (!($name = $status->getName()) || !isset($this->strategies[$status->getName()])) {
            return null;
        }

        return $this->strategies[$name];
    }
}
