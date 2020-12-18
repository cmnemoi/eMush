<?php

namespace Mush\Status\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Status\Entity\Status;

class StatusCycleHandlerService implements StatusCycleHandlerServiceInterface
{
    private array $strategies = [];

    public function addStrategy(AbstractCycleHandler $cycleHandler): void
    {
        $this->strategies[$cycleHandler->getName()] = $cycleHandler;
    }

    public function getStatusCycleHandler(Status $status): ?AbstractCycleHandler
    {
        if (!isset($this->strategies[$status->getName()])) {
            return null;
        }

        return $this->strategies[$status->getName()];
    }
}
