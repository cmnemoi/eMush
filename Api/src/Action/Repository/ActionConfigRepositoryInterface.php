<?php

declare(strict_types=1);

namespace Mush\Action\Repository;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;

interface ActionConfigRepositoryInterface
{
    public function findActionSuccessRateByDaedalusAndMechanicOrThrow(ActionEnum $action, Daedalus $daedalus, string $mechanic): int;
}
