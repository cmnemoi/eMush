<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

interface D100RollServiceInterface
{
    public function isSuccessful(int $successRate): bool;

    public function isAFailure(int $successRate): bool;
}
