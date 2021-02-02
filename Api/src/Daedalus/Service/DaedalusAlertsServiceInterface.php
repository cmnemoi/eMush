<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusAlertsServiceInterface
{
    public function getAlerts(Daedalus $daedalus): array;
}
