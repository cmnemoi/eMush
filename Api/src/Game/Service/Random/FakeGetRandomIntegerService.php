<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class FakeGetRandomIntegerService implements GetRandomIntegerServiceInterface
{
    public function __construct(private int $result) {}

    public function execute(int $min, int $max): int
    {
        return $this->result;
    }
}
