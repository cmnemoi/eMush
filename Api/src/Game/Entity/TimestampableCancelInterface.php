<?php

declare(strict_types=1);

namespace Mush\Game\Entity;

interface TimestampableCancelInterface
{
    public function isTimestampableCanceled(): bool;
}
