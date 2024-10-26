<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Enum\PlayerVariableEnum;

final class RemoveMovementPointsFromPlayerService extends AbstractRemoveVariableFromPlayerService implements RemoveMovementPointsFromPlayerServiceInterface
{
    protected function variableName(): string
    {
        return PlayerVariableEnum::MOVEMENT_POINT;
    }
}
