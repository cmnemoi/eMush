<?php

declare(strict_types=1);

namespace Mush\Game\Service\Random;

final class FakeRandomFloatService implements RandomFloatServiceInterface
{
    private float $result = 0;

    public function generateBetween(float $min, float $max): float
    {
        return $this->result;
    }

    public function setResult(float $result): void
    {
        $this->result = $result;
    }
}
