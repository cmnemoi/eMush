<?php

namespace Mush\Action\Service;

interface SuccessRateServiceInterface
{
    public function getSuccessRate(
        int $baseRate,
        int $numberOfAttempt,
        float $relativeModificator,
        float $fixedModificator = 0
    ): int;
}
