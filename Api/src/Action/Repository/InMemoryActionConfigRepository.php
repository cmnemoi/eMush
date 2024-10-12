<?php

declare(strict_types=1);

namespace Mush\Action\Repository;

use Mush\Action\ConfigData\ActionData;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;

final class InMemoryActionConfigRepository implements ActionConfigRepositoryInterface
{
    public function findActionSuccessRateByDaedalusAndMechanicOrThrow(ActionEnum $action, Daedalus $daedalus, string $mechanic): int
    {
        return ActionData::getByName($action)['percentageSuccess']['value'];
    }
}
