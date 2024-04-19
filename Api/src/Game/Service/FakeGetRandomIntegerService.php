<?php

declare(strict_types=1);

namespace Mush\Game\Service;

class FakeGetRandomIntegerService implements GetRandomIntegerServiceInterface
{
    public function __construct(private $result) {}

    public function execute(int $min, int $max): int
    {
        return $this->result;
    }
}
