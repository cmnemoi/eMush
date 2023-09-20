<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Enum\DaedalusVariableEnum;

final class InsertFuelChamber extends InsertAction
{
    protected string $name = ActionEnum::INSERT_FUEL_CHAMBER;

    protected function getDaedalusVariable(): string
    {
        return DaedalusVariableEnum::COMBUSTION_CHAMBER_FUEL;
    }
}
