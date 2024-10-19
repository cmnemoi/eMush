<?php

declare(strict_types=1);

namespace Mush\Player\Service;

use Mush\Player\Enum\PlayerVariableEnum;

final class RemoveHealthFromPlayerService extends AbstractRemoveVariableFromPlayerService implements RemoveHealthFromPlayerServiceInterface
{
    protected function variableName(): string
    {
        return PlayerVariableEnum::HEALTH_POINT;
    }
}
