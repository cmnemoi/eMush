<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class D100RollService implements D100RollServiceInterface
{
    public function __construct(private GetRandomIntegerServiceInterface $getRandomInteger) {}

    public function isAFailure(int $failureRate): bool
    {
        $roll = $this->getRandomInteger->execute(1, 100);

        return $roll > $failureRate;
    }
}
