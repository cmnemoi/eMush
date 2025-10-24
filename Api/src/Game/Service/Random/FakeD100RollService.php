<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class FakeD100RollService implements D100RollServiceInterface
{
    public function __construct(private bool $isSuccessful = true) {}

    public function isSuccessful(int $successRate): bool
    {
        return $this->isSuccessful;
    }

    public function isAFailure(int $successRate): bool
    {
        return $this->isSuccessful === false;
    }

    public function makeSuccessful(): static
    {
        $this->isSuccessful = true;

        return $this;
    }

    public function makeFail(): static
    {
        $this->isSuccessful = false;

        return $this;
    }
}
