<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Enum\PlayerVariableEnum;

final class RemoveActionPointsFromPlayerService extends AbstractRemoveVariableFromPlayerService implements RemoveActionPointsFromPlayerServiceInterface
{
    protected function variableName(): string
    {
        return PlayerVariableEnum::ACTION_POINT;
    }
}
