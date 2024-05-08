<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

interface D100RollServiceInterface
{
    public function isAFailure(int $failureRate): bool;
}
