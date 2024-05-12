<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class FakeD100RollService implements D100RollServiceInterface
{
    public function __construct(private bool $isAFailure) {}

    public function isAFailure(int $failureRate): bool
    {
        return $this->isAFailure;
    }
}
