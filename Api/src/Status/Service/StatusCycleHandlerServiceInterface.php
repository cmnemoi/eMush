<?php

namespace Mush\Status\Service;

use Mush\Game\CycleHandler\AbstractCycleHandler;
use Mush\Status\Entity\Status;

interface StatusCycleHandlerServiceInterface
{
    public function getStatusCycleHandler(Status $status): ?AbstractCycleHandler;
}
