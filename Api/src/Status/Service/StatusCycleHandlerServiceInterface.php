<?php

namespace Mush\Status\Service;

use Mush\Status\CycleHandler\AbstractStatusCycleHandler;
use Mush\Status\Entity\Status;

interface StatusCycleHandlerServiceInterface
{
    public function getStatusCycleHandler(Status $status): ?AbstractStatusCycleHandler;
}
