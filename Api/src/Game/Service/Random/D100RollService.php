<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class D100RollService implements D100RollServiceInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomInteger) {}

    public function isSuccessful(int $successRate): bool
    {
        $roll = $this->getRandomInteger->execute(1, 100);

        return $roll <= $successRate;
    }

    public function isAFailure(int $successRate): bool
    {
        return $this->isSuccessful($successRate) === false;
    }
}
