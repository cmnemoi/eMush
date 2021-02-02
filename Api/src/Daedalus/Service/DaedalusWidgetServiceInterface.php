<?php

namespace Mush\Daedalus\Service;

use Mush\Daedalus\Entity\Daedalus;

interface DaedalusWidgetServiceInterface
{
    public function getAlerts(Daedalus $daedalus): array;

    public function getMinimap(Daedalus $daedalus): array;
}
